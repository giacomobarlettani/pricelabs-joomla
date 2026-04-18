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

/**
 * Aggregates a list of RAR commands to simultaneously execute them at once.
 *
 * @since 1.0
 */
class RARGroupExecutionCommand extends GroupExecutionCommand
{
    /**
     * @var array
     */
    protected array $options;

    /**
     * Class constructor.
     *
     * @param  Command[]  $commands
     * @param  array      $options   A configuration array holding:
     *                               - room_id
     *                               - date_from
     *                               - date_to
     *                               - rate_id
     */
    public function __construct(array $commands, array $options)
    {
        parent::__construct($commands);

        $this->options = $options;
    }

    /**
     * @inheritDoc
     */
    public function execute(): Result
    {
        if (method_exists('VikBooking', 'preloadSeasonRecords'))
        {
            \VikBooking::preloadSeasonRecords(
                [$this->options['room_id']],
                strtotime($this->options['date_from']),
                strtotime($this->options['date_to'])
            );
        }

        $result = parent::execute();

        if (method_exists('VikBooking', 'preloadSeasonRecords'))
        {
            \VikBooking::preloadSeasonRecords([$this->options['room_id']], false);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    protected function preflight(Command $command): void
    {
        if (!$command instanceof RARCommand)
        {
            throw new \InvalidArgumentException(
                'Only RARCommands are supported, ' . get_class($command) . ' given.',
                406
            );
        }
    }
}