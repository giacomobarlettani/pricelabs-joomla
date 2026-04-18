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
 * E4jConnect Pricelabs Result interface.
 *
 * @since 1.0
 */
interface Result
{
    /**
     * Returns a list of successful operations.
     *
     * @return  array
     */
    public function getSuccesses(): array;

    /**
     * Returns a list of erroneous operations.
     *
     * @return  array
     */
    public function getErrors(): array;

    /**
     * Returns a list of operations with warnings.
     *
     * @return  array
     */
    public function getWarnings(): array;
}