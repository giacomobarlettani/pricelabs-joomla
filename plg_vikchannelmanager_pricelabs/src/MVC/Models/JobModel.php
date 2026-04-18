<?php
/**
 * @package     Pricelabs E4jConnect-VikBooking
 * @author      Giacomo Barlettani
 * @copyright   Copyright (C) 2026 Giacomo Barlettani All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://www.campetroso.it
 */

namespace Joomla\Plugin\Vikchannelmanager\Pricelabs\MVC\Models;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * E4jConnect Pricelabs jobs model.
 *
 * @since 1.0
 */
class JobModel extends BaseDatabaseModel
{
    /**
     * Returns the table instance for this model.
     *
     * @return  \Joomla\Plugin\Vikchannelmanager\Pricelabs\MVC\Tables\JobTable
     */
    public function getTable($name = '', $prefix = '', $options = []): \Joomla\Plugin\Vikchannelmanager\Pricelabs\MVC\Tables\JobTable
    {
        return new \Joomla\Plugin\Vikchannelmanager\Pricelabs\MVC\Tables\JobTable(
            Factory::getDbo()
        );
    }

    /**
     * Saves a job record.
     *
     * @param   array|object  $data
     *
     * @return  int|false  The record ID on success, false otherwise.
     */
    public function save(array|object $data): int|false
    {
        $data  = (array) $data;
        $table = $this->getTable();

        if (!empty($data['id']))
        {
            $table->load((int) $data['id']);
        }

        if (!$table->bind($data))
        {
            $this->setError($table->getError());
            return false;
        }

        if (!$table->check())
        {
            $this->setError($table->getError());
            return false;
        }

        if (!$table->store())
        {
            $this->setError($table->getError());
            return false;
        }

        return (int) $table->id;
    }

    /**
     * Deletes job records by ID.
     *
     * @param   array  $ids
     *
     * @return  bool
     */
    public function delete(array $ids): bool
    {
        if (!$ids)
        {
            return false;
        }

        $table = $this->getTable();

        foreach ($ids as $id)
        {
            if (!$table->delete((int) $id))
            {
                $this->setError($table->getError());
                return false;
            }
        }

        return true;
    }

    /**
     * Attempts to get the next job to process.
     *
     * @return  object|null
     */
    public function getNextJob(): ?object
    {
        $db = Factory::getDbo();

        $db->setQuery(
            $db->getQuery(true)
                ->select('*')
                ->from($db->qn('#__e4jconnect_pricelabs_jobs'))
                ->where($db->qn('status') . ' IS NULL')
                ->where($db->qn('executedon') . ' IS NULL')
                ->order($db->qn('createdon') . ' ASC')
                ->order($db->qn('id') . ' ASC'),
            0,
            1
        );

        $job = $db->loadObject();

        if ($job)
        {
            $job->command = unserialize((string) $job->command);
        }

        return $job ?: null;
    }

    /**
     * Stores an error message.
     *
     * @param   string|\Exception  $error
     *
     * @return  void
     */
    protected function setError(string|\Exception $error): void
    {
        if ($error instanceof \Exception)
        {
            parent::setError($error->getMessage());
            return;
        }

        parent::setError($error);
    }

    /**
     * Returns the last error message.
     *
     * @return  mixed
     */
    public function getError($i = null, $toString = true): mixed
    {
        return parent::getError($i, $toString);
    }
}