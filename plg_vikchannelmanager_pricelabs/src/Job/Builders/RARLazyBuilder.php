<?php
/**
 * @package     Pricelabs E4jConnect-VikBooking
 * @author      Giacomo Barlettani
 * @copyright   Copyright (C) 2026 Giacomo Barlettani All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://www.campetroso.it
 */

namespace Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Builders;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Command;
use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Commands\RARCommand;
use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Commands\RARGroupExecutionCommand;
use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Commands\RARBulkCommand;
use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Commands\OptimizationCommand;

/**
 * E4jConnect Pricelabs lazy RARBuilder implementation.
 * Rate updates are applied only to the website first. Once all prices are
 * updated, a bulk action is invoked to notify all supported OTAs, only if
 * explicitly requested by PriceLabs.
 *
 * @since 1.0
 */
class RARLazyBuilder
{
    /**
     * @var string|null
     */
    protected ?string $apiUser;

    /**
     * Class constructor.
     *
     * @param  string|null  $user
     */
    public function __construct(?string $user = null)
    {
        $this->apiUser = $user;
    }

    /**
     * Builds a list of commands from the request.
     *
     * @param   \Joomla\CMS\Input\Input  $input
     *
     * @return  Command[]
     */
    public function getCommandsFromRequest(\Joomla\CMS\Input\Input $input): array
    {
        // gather default request data
        $default_data = [
            'room_id'  => $input->getInt('room_id', 0),
            'upd_vbo'  => $input->getInt('upd_vbo', 1),
            // never update OTAs directly
            'upd_otas' => 0,
        ];

        $commands = $executors = [];

        // set up bounds for the RAR bulk command
        $bounds = [
            'room_id'   => $default_data['room_id'],
            'date_from' => null,
            'date_to'   => null,
        ];

        // calculate the timestamp for today at midnight
        $today_midnight_ts = strtotime(date('Y-m-d'));

        // scan jobs list
        foreach ($input->get('rates_data', [], 'array') as $rates_data)
        {
            // normalize request properties
            if (isset($rates_data['fdate']) && isset($rates_data['tdate']))
            {
                $rates_data['date_from'] = $rates_data['fdate'];
                $rates_data['date_to']   = $rates_data['tdate'];
                unset($rates_data['fdate'], $rates_data['tdate']);
            }

            // prevent past dates from being processed
            if (($rates_data['date_from'] ?? '') && ($rates_data['date_to'] ?? ''))
            {
                if (strtotime($rates_data['date_from']) < $today_midnight_ts)
                {
                    $rates_data['date_from'] = date('Y-m-d');
                }

                if (strtotime($rates_data['date_from']) > strtotime($rates_data['date_to']))
                {
                    // skip invalid request
                    continue;
                }
            }

            // adjust command data
            $command_data = [
                'date_from'  => $rates_data['date_from'] ?? '',
                'date_to'    => $rates_data['date_to'] ?? '',
                'minlos'     => $rates_data['minlos'] ?? 0,
                'maxlos'     => $rates_data['maxlos'] ?? 0,
                'cta'        => $rates_data['cta'] ?? null,
                'ctd'        => $rates_data['ctd'] ?? null,
                'cta_wdays'  => $rates_data['cta_wdays'] ?? [],
                'ctd_wdays'  => $rates_data['ctd_wdays'] ?? [],
                'rates_data' => [
                    [
                        'rate_id' => $rates_data['rate_id'] ?? null,
                        'cost'    => $rates_data['cost'] ?? 0,
                    ],
                ],
            ];

            // push command to apply the rate change only to VikBooking
            $executors[] = new RARCommand(
                array_merge($default_data, $command_data),
                $this->apiUser
            );

            // extend the window in case the from date is lower than the registered one
            if ($bounds['date_from'] === null || $bounds['date_from'] > $command_data['date_from'])
            {
                $bounds['date_from'] = $command_data['date_from'];
            }

            // extend the window in case the to date is higher than the registered one
            if ($bounds['date_to'] === null || $bounds['date_to'] < $command_data['date_to'])
            {
                $bounds['date_to'] = $command_data['date_to'];
            }

            // update rate plan ID in bounds
            $bounds['rate_id'] = $rates_data['rate_id'] ?? null;
        }

        // internally update all rate variations at once
        $commands[] = new RARGroupExecutionCommand($executors, $bounds);

        // check if we should notify the OTAs about the price changes
        if ($input->getBool('upd_otas', false))
        {
            // invoke an auto-bulk-action (rates) to update all channels
            $commands[] = new RARBulkCommand($bounds);
        }
        else
        {
            /**
             * Schedule command for database records optimization only if
             * NO OTAs should be updated. If this command was scheduled together
             * with RARBulkCommand, the risk would be that db records may be under
             * removal/update through OptimizationCommand still running.
             *
             * @requires VCM >= 1.9.10
             *
             * @since 1.0
             */
            $commands[] = new OptimizationCommand($bounds);
        }

        return $commands;
    }
}