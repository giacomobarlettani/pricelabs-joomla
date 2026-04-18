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
use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Result;
use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Results\JsonResult;

/**
 * E4jConnect Pricelabs RARBulkCommand implementation.
 * Submits a bulk action to all the channels for a specific room on a given window.
 *
 * @since 1.0
 */
class RARBulkCommand implements Command
{
    /**
     * @var array
     */
    protected array $options;

    /**
     * Class constructor.
     *
     * @param  array  $options  A configuration array holding:
     *                          - room_id
     *                          - date_from
     *                          - date_to
     *                          - rate_id
     */
    public function __construct(array $options)
    {
        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function getSummary(): string
    {
        return 'Updating rates on the OTAs.';
    }

    /**
     * @inheritDoc
     */
    public function getExtraData(): string
    {
        return sprintf(
            'Sending bulk actions from %s to %s (Listing #%d).',
            $this->options['date_from'] ?? '/',
            $this->options['date_to'] ?? '/',
            $this->options['room_id'] ?? '/'
        );
    }

    /**
     * @inheritDoc
     */
    public function execute(): Result
    {
        $result = [
            'success' => [],
            'errors'  => [],
        ];

        if (empty($this->options['room_id']))
        {
            $result['errors'][] = 'Missing room ID.';
        }

        if (empty($this->options['date_from']))
        {
            $result['errors'][] = 'Missing from date.';
        }

        if (empty($this->options['date_to']))
        {
            $result['errors'][] = 'Missing to date.';
        }

        if ($result['errors'])
        {
            return new JsonResult($result);
        }

        try
        {
            $processor = new \VCMBulkactionProcessor([
                'from'         => $this->options['date_from'],
                'to'           => $this->options['date_to'],
                'forced_rooms' => (int) $this->options['room_id'],
                'update'       => 'rates',
                'rate_id'      => $this->options['rate_id'] ?? 0,
                'notifications' => true,
            ]);

            $processor->distributeRates();

            $result['success'][] = 'Rates updated on OTAs. Check the results from the channel manager dashboard.';
        }
        catch (\Throwable $e)
        {
            $result['errors'][] = sprintf(
                'Could not update rates on OTAs (%d): %s',
                $e->getCode() ?: 500,
                $e->getMessage()
            );
        }

        return new JsonResult($result);
    }
}