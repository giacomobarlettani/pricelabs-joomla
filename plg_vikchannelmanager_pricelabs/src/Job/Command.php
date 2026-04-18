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
 * E4jConnect Pricelabs Command interface.
 *
 * @since 1.0
 */
interface Command
{
    /**
     * Returns the command summary.
     *
     * @return  string
     */
    public function getSummary(): string;

    /**
     * Returns the command extra data.
     *
     * @return  string
     */
    public function getExtraData(): string;

    /**
     * Executes the command.
     *
     * @return  Result
     */
    public function execute(): Result;
}