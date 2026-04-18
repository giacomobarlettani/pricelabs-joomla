<?php
/**
 * @package     Pricelabs E4jConnect-VikBooking
 * @author      Giacomo Barlettani
 * @copyright   Copyright (C) 2026 Giacomo Barlettani All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://www.campetroso.it
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;

/**
 * Installation script for plg_task_pricelabs.
 *
 * @since 1.0
 */
class PlgTaskPricelabsInstallerScript
{
    /**
     * Called after any installation action.
     *
     * @param   string            $type
     * @param   InstallerAdapter  $adapter
     *
     * @return  void
     */
    public function postflight(string $type, InstallerAdapter $adapter): void
    {
        if ($type === 'install' || $type === 'update')
        {
            $this->enablePlugin();
        }
    }

    /**
     * Enables the plugin after installation.
     *
     * @return  void
     */
    protected function enablePlugin(): void
    {
        $db = Factory::getDbo();

        $db->setQuery(
            $db->getQuery(true)
                ->update($db->qn('#__extensions'))
                ->set($db->qn('enabled') . ' = 1')
                ->where($db->qn('type') . ' = ' . $db->q('plugin'))
                ->where($db->qn('folder') . ' = ' . $db->q('task'))
                ->where($db->qn('element') . ' = ' . $db->q('pricelabs'))
        );

        $db->execute();
    }
}  