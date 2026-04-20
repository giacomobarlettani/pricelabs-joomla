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
 * Installation script for pkg_pricelabs.
 *
 * @since 1.0
 */
class PkgPricelabsInstallerScript
{
    protected string $minVikBooking = '1.6.7';
    protected string $minVikChannelManager = '1.8.24';
    protected string $minJoomla = '5.0.0';
    protected string $minPhp = '8.1.0';

    public function preflight(string $type, InstallerAdapter $adapter): bool
    {
        if (version_compare(PHP_VERSION, $this->minPhp, '<'))
        {
            Factory::getApplication()->enqueueMessage(
                sprintf('PHP >= %s is required. You have %s.', $this->minPhp, PHP_VERSION),
                'error'
            );
            return false;
        }

        if (version_compare(JVERSION, $this->minJoomla, '<'))
        {
            Factory::getApplication()->enqueueMessage(
                sprintf('Joomla >= %s is required. You have %s.', $this->minJoomla, JVERSION),
                'error'
            );
            return false;
        }

        if (!$this->checkExtension('com_vikbooking', $this->minVikBooking))
        {
            Factory::getApplication()->enqueueMessage(
                sprintf('VikBooking >= %s must be installed and enabled.', $this->minVikBooking),
                'error'
            );
            return false;
        }

        if (!$this->checkExtension('com_vikchannelmanager', $this->minVikChannelManager))
        {
            Factory::getApplication()->enqueueMessage(
                sprintf('Vik Channel Manager >= %s must be installed and enabled.', $this->minVikChannelManager),
                'error'
            );
            return false;
        }

        return true;
    }

    public function install(InstallerAdapter $adapter): void
    {
        Factory::getApplication()->enqueueMessage(
            'Pricelabs E4jConnect-VikBooking package installed successfully!',
            'message'
        );
    }

    public function update(InstallerAdapter $adapter): void
    {
        Factory::getApplication()->enqueueMessage(
            'Pricelabs E4jConnect-VikBooking package updated successfully!',
            'message'
        );
    }

    public function uninstall(InstallerAdapter $adapter): void
    {
        Factory::getApplication()->enqueueMessage(
            'Pricelabs E4jConnect-VikBooking package uninstalled.',
            'message'
        );
    }

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
}