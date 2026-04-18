<?php
/**
 * @package     Pricelabs E4jConnect-VikBooking
 * @author      Giacomo Barlettani
 * @copyright   Copyright (C) 2026 Giacomo Barlettani All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://www.campetroso.it
 */

namespace Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Commands;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Command;
use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Results\JsonResult;

/**
 * E4jConnect Pricelabs RARCommand implementation.
 *
 * @since 1.0
 */
class RARCommand implements Command
{
    /**
     * @var array
     */
    protected array $data;

    /**
     * @var string|null
     */
    protected ?string $apiUser;

    /**
     * Class constructor.
     *
     * @param  array        $data
     * @param  string|null  $apiUser
     */
    public function __construct(array $data, ?string $apiUser = null)
    {
        $this->data    = $data;
        $this->apiUser = $apiUser;
    }

    /**
     * @inheritDoc
     */
    public function getSummary(): string
    {
        if ($this->data['date_from'] == $this->data['date_to'])
        {
            return sprintf(
                "Update Room Rates, %s\n%s",
                \VikBooking::getCurrencySymb() . ' ' . \VikBooking::numberFormat($this->data['rates_data'][0]['cost']),
                $this->data['date_from']
            );
        }

        return sprintf(
            "Update Room Rates, %s\n%s - %s",
            \VikBooking::getCurrencySymb() . ' ' . \VikBooking::numberFormat($this->data['rates_data'][0]['cost']),
            $this->data['date_from'],
            $this->data['date_to']
        );
    }

    /**
     * @inheritDoc
     */
    public function getExtraData(): string
    {
        // week days map
        $wdays_map = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        // get room data
        $room = \VikBooking::getRoomInfo($this->data['room_id'], ['id', 'name']);

        // build restrictions, if any
        $restrictions = [];

        if (isset($this->data['minlos']) && $this->data['minlos'] > 0)
        {
            $restrictions[] = sprintf('Min LOS %d', $this->data['minlos']);
        }

        if (isset($this->data['maxlos']) && $this->data['maxlos'] > 0)
        {
            $restrictions[] = sprintf('Max LOS %d', $this->data['maxlos']);
        }

        if (isset($this->data['cta']) && $this->data['cta'])
        {
            $restrictions[] = 'CTA';
        }
        elseif (!empty($this->data['cta_wdays']))
        {
            $cta_wdays = array_filter(array_map(function($wday) use ($wdays_map) {
                $index = array_search($wday, array_keys($wdays_map));
                return $index !== false ? $wdays_map[$index] : '';
            }, $this->data['cta_wdays']));

            if ($cta_wdays)
            {
                $restrictions[] = sprintf('CTA: %s', implode(', ', $cta_wdays));
            }
        }

        if (isset($this->data['ctd']) && $this->data['ctd'])
        {
            $restrictions[] = 'CTD';
        }
        elseif (!empty($this->data['ctd_wdays']))
        {
            $ctd_wdays = array_filter(array_map(function($wday) use ($wdays_map) {
                $index = array_search($wday, array_keys($wdays_map));
                return $index !== false ? $wdays_map[$index] : '';
            }, $this->data['ctd_wdays']));

            if ($ctd_wdays)
            {
                $restrictions[] = sprintf('CTD: %s', implode(', ', $ctd_wdays));
            }
        }

        return preg_replace(
            "/,\s$/",
            '',
            sprintf('%s, %s, %s', $this->apiUser ?: '', $room['name'] ?? '', implode(', ', $restrictions))
        );
    }

    /**
     * @inheritDoc
     */
    public function execute(): \Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Result
    {
        list($channels, $success, $warnings, $errors) = \VCMOtaRarUpdate::getInstance($this->data, $anew = true)
            ->setCaller('App')
            ->setApiUser($this->apiUser)
            ->execute();

        $success = array_merge(
            ['New rates applied to the booking engine.'],
            (array) $success
        );

        return new JsonResult([
            'channels' => (array) $channels,
            'success'  => (array) $success,
            'warnings' => (array) $warnings,
            'errors'   => (array) $errors,
        ]);
    }
}