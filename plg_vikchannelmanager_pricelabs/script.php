<?php
/**
 * @package     Pricelabs E4jConnect-VikBooking
 * @author      Giacomo Barlettani
 * @copyright   Copyright (C) 2026 Giacomo Barlettani All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://www.campetroso.it
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;

/**
 * Installation script for plg_vikchannelmanager_pricelabs.
 * Handles install, update and uninstall routines.
 *
 * @since 1.0
 */
class PlgVikchannelmanagerPricelabsInstallerScript
{
    /**
     * Minimum required version of VikBooking.
     *
     * @var string
     */
    protected string $minVikBooking = '1.6.7';

    /**
     * Minimum required version of VikChannelManager.
     *
     * @var string
     */
    protected string $minVikChannelManager = '1.8.24';

    /**
     * Called before any installation action.
     *
     * @param   string            $type     The type of action: install, update or discover_install.
     * @param   InstallerAdapter  $adapter  The installer adapter.
     *
     * @return  bool  True to proceed, false to abort.
     */
    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        // check VikBooking is installed and active
        if (!$this->checkExtension('com_vikbooking', $this->minVikBooking))
        {
            Factory::getApplication()->enqueueMessage(
                'VikBooking >= ' . $this->minVikBooking . ' must be installed and enabled.',
                'error'
            );

            return false;
        }

        // check VikChannelManager is installed and active
        if (!$this->checkExtension('com_vikchannelmanager', $this->minVikChannelManager))
        {
            Factory::getApplication()->enqueueMessage(
                'Vik Channel Manager >= ' . $this->minVikChannelManager . ' must be installed and enabled.',
                'error'
            );

            return false;
        }

        return true;
    }

    /**
     * Called after a successful installation.
     *
     * @param   InstallerAdapter  $adapter  The installer adapter.
     *
     * @return  void
     */
    public function install(InstallerAdapter $adapter): void
    {
        Factory::getApplication()->enqueueMessage(
            'Pricelabs E4jConnect-VikBooking plugin installed successfully!',
            'message'
        );
    }

    /**
     * Called after a successful update.
     *
     * @param   InstallerAdapter  $adapter  The installer adapter.
     *
     * @return  void
     */
    public function update(InstallerAdapter $adapter): void
    {
        Factory::getApplication()->enqueueMessage(
            'Pricelabs E4jConnect-VikBooking plugin updated successfully!',
            'message'
        );
    }

    /**
     * Called after uninstallation.
     *
     * @param   InstallerAdapter  $adapter  The installer adapter.
     *
     * @return  void
     */
    public function uninstall(InstallerAdapter $adapter): void
    {
        Factory::getApplication()->enqueueMessage(
            'Pricelabs E4jConnect-VikBooking plugin uninstalled.',
            'message'
        );
    }

    /**
     * Called after any installation action.
     *
     * @param   string            $type     The type of action.
     * @param   InstallerAdapter  $adapter  The installer adapter.
     *
     * @return  void
     */
    public function postflight(string $type, InstallerAdapter $adapter): void
    {
        if ($type === 'install' || $type === 'update')
        {
            // make sure the plugin is enabled after install/update
            $this->enablePlugin();
        }
    }

    /**
     * Checks whether a Joomla extension is installed and meets the minimum version.
     *
     * @param   string  $element   The extension element name (e.g. com_vikbooking).
     * @param   string  $minVersion  The minimum required version.
     *
     * @return  bool
     */
    protected function checkExtension(string $element, string $minVersion): bool
    {
        $db = Factory::getDbo();

        $db->setQuery(
            $db->getQuery(true)
                ->select($db->qn(['manifest_cache', 'enabled']))
                ->from($db->qn('#__extensions'))
                ->where($db->qn('element') . ' = ' . $db->q($element))
                ->where($db->qn('type') . ' = ' . $db->q('component'))
        );

        $ext = $db->loadObject();

        if (!$ext || !$ext->enabled)
        {
            return false;
        }

        $manifest = json_decode($ext->manifest_cache);

        if (!$manifest || empty($manifest->version))
        {
            return false;
        }

        return version_compare($manifest->version, $minVersion, '>=');
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
                ->where($db->qn('folder') . ' = ' . $db->q('vikchannelmanager'))
                ->where($db->qn('element') . ' = ' . $db->q('pricelabs'))
        );

        $db->execute();
    }
}