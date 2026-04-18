<?php
/**
 * @package     Pricelabs E4jConnect-VikBooking
 * @author      Giacomo Barlettani
 * @copyright   Copyright (C) 2026 Giacomo Barlettani All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://www.campetroso.it
 */

namespace Joomla\Plugin\Vikchannelmanager\Pricelabs\Job;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * E4jConnect Pricelabs Collection interface.
 *
 * @since 1.0
 */
interface Collection
{
    /**
     * Creates a queue of jobs.
     *
     * @param   string       $id     Queue signature ID.
     * @param   string|null  $event  Queue event type.
     *
     * @return  void
     */
    public function createQueue(string $id, ?string $event = null): void;

    /**
     * Adds a command to the currently registered queue.
     *
     * @param   Command  $command
     *
     * @return  void
     */
    public function push(Command $command): void;

    /**
     * Returns the next executable command.
     *
     * @return  Command|null
     */
    public function pull(): ?Command;

    /**
     * Updates the execution results.
     *
     * @param   Command  $command
     * @param   Result   $result
     *
     * @return  void
     */
    public function complete(Command $command, Result $result): void;
}