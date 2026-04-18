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
 * E4jConnect Pricelabs OptimizationCommand implementation.
 * Optimizes the database records for the newly created alteration rules.
 *
 * @since 1.0
 */
class OptimizationCommand implements Command
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
        return 'Optimizing database records.';
    }

    /**
     * @inheritDoc
     */
    public function getExtraData(): string
    {
        return sprintf(
            'Optimized records from %s to %s (Listing #%d).',
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

        if (empty($this->options['rate_id']))
        {
            $result['errors'][] = 'Missing rate plan ID.';
        }

        if ($result['errors'])
        {
            return new JsonResult($result);
        }

        try
        {
            \VBOPerformanceCleaner::setOptions([
                'listing_id' => (int) $this->options['room_id'],
                'id_price'   => (int) $this->options['rate_id'],
                'from_date'  => $this->options['date_from'],
                'to_date'    => $this->options['date_to'],
            ]);

            \VBOPerformanceCleaner::listingSeasonSnapshot();

            $result['success'][] = 'Database records optimized.';
        }
        catch (\Throwable $e)
        {
            $result['errors'][] = sprintf(
                'Database records optimization failed (%d): %s',
                $e->getCode() ?: 500,
                $e->getMessage()
            );
        }

        return new JsonResult($result);
    }
}