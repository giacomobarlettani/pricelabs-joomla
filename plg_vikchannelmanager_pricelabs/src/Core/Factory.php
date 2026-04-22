<?php
/**
 * @package     Pricelabs E4jConnect-VikBooking
 * @author      Giacomo Barlettani
 * @copyright   Copyright (C) 2026 Giacomo Barlettani All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://www.campetroso.it
 */

namespace Joomla\Plugin\Vikchannelmanager\Pricelabs\Core;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\DI\Container;

/**
 * Platform factory class.
 *
 * @since 1.0
 */
abstract class Factory
{
    /**
     * Global container object.
     *
     * @var Container|null
     */
    protected static ?Container $container = null;

    /**
     * Returns the global service container object, only creating it if it doesn't already exist.
     *
     * @return  Container
     */
    public static function getContainer(): Container
    {
        if (!static::$container)
        {
            static::$container = static::createContainer();
        }

        return static::$container;
    }

    /**
     * Creates the global container object.
     *
     * @return  Container
     */
    protected static function createContainer(): Container
    {
        $container = new Container;

        // register job processor
	$container->set(
    	'job.processor',
    	function(Container $c)
    	{
        return new \Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Processor(
            new \Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Collections\DatabaseCollection
        	);
    	},
    	true,
    	false
	);
        return $container;
    }
}
