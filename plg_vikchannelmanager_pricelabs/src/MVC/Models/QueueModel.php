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
 * E4jConnect Pricelabs queue model.
 *
 * @since 1.0
 */
class QueueModel extends BaseDatabaseModel
{
    /**
     * Returns the table instance for this model.
     *
     * @return  \Joomla\Plugin\Vikchannelmanager\Pricelabs\MVC\Tables\QueueTable
     */
    public function getTable($name = '', $prefix = '', $options = [])
    {
        return new \Joomla\Plugin\Vikchannelmanager\Pricelabs\MVC\Tables\QueueTable(
            Factory::getDbo()
        );
    }

    /**
     * Saves a queue record.
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
     * Returns a single queue item by ID or by filter array.
     *
     * @param   int|array  $id
     *
     * @return  object|null
     */
    public function getItem(int|array $id): ?object
    {
        $table = $this->getTable();

        if (is_array($id))
        {
            $db = Factory::getDbo();

            $q = $db->getQuery(true)
                ->select('*')
                ->from($db->qn('#__e4jconnect_pricelabs_queue'));

            foreach ($id as $col => $val)
            {
                $q->where($db->qn($col) . ' = ' . $db->q($val));
            }

            $db->setQuery($q, 0, 1);

            return $db->loadObject() ?: null;
        }

        return $table->load((int) $id) ? $table : null;
    }

    /**
     * Deletes queue records and their related jobs.
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

        $db = Factory::getDbo();

        $table = $this->getTable();

        foreach ($ids as $id)
        {
            if (!$table->delete((int) $id))
            {
                $this->setError($table->getError());
                return false;
            }
        }

        // delete children jobs
        $db->setQuery(
            $db->getQuery(true)
                ->select($db->qn('id'))
                ->from($db->qn('#__e4jconnect_pricelabs_jobs'))
                ->where($db->qn('id_queue') . ' IN (' . implode(',', array_map('intval', $ids)) . ')')
        );

        $jobIds = $db->loadColumn();

        if ($jobIds)
        {
            (new JobModel)->delete($jobIds);
        }

        return true;
    }

    /**
     * Returns the summary of a queue.
     *
     * @param   string  $queueId
     *
     * @return  object
     *
     * @throws  \DomainException
     */
    public function getSummary(string $queueId): object
    {
        $queue = $this->getItem(['signature' => $queueId]);

        if (!$queue)
        {
            throw new \DomainException('Queue not found', 404);
        }

        $queue->tot_success  = 0;
        $queue->tot_warnings = 0;
        $queue->tot_errors   = 0;
        $queue->jobs         = [];

        $db = Factory::getDbo();

        $db->setQuery(
            $db->getQuery(true)
                ->select($db->qn([
                    'command',
                    'createdon',
                    'results',
                    'executedon',
                    'status',
                ]))
                ->from($db->qn('#__e4jconnect_pricelabs_jobs'))
                ->where($db->qn('id_queue') . ' = ' . (int) $queue->id)
                ->order($db->qn('id') . ' ASC')
        );

        foreach ($db->loadObjectList() as $job)
        {
            $job->command = unserialize((string) $job->command);

            if ($job->command instanceof \Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Command)
            {
                $job->extraData = $job->command->getExtraData();
                $job->command   = $job->command->getSummary();
            }
            else
            {
                $job->extraData = '';
                $job->command   = '/';
            }

            $job->results = json_decode($job->results ?: '{}');

            $queue->jobs[] = $job;

            $queue->tot_success  += ($job->results->success ?? []) ? 1 : 0;
            $queue->tot_warnings += ($job->results->warnings ?? []) ? 1 : 0;
            $queue->tot_errors   += ($job->results->errors ?? []) ? 1 : 0;
        }

        return $queue;
    }

    /**
     * Aborts an ongoing queue and its related jobs.
     *
     * @param   string  $queueId
     *
     * @return  void
     *
     * @throws  \DomainException
     */
    public function abort(string $queueId): void
    {
        $db = Factory::getDbo();

        $queue = $this->getItem(['signature' => $queueId]);

        if (!$queue)
        {
            throw new \DomainException('Queue not found', 404);
        }

        $this->save([
            'id'      => $queue->id,
            'aborted' => 1,
        ]);

        // set all pending jobs as aborted (status = 3)
        $db->setQuery(
            $db->getQuery(true)
                ->update($db->qn('#__e4jconnect_pricelabs_jobs'))
                ->set($db->qn('status') . ' = 3')
                ->where($db->qn('id_queue') . ' = ' . (int) $queue->id)
                ->where($db->qn('status') . ' IS NULL')
        );

        $db->execute();
    }

    /**
     * Deletes all completed queues older than the given threshold.
     *
     * @param   string  $threshold
     *
     * @return  bool
     */
    public function flush(string $threshold = '-1 month'): bool
    {
        try
        {
            $threshold = Factory::getDate($threshold);
        }
        catch (\Exception $e)
        {
            $threshold = Factory::getDate('-1 month');
        }

        $db = Factory::getDbo();

        $db->setQuery(
            $db->getQuery(true)
                ->select($db->qn('id'))
                ->from($db->qn('#__e4jconnect_pricelabs_queue'))
                ->where($db->qn('createdon') . ' < ' . $db->q($threshold->toSql()))
                ->extendWhere('AND', [
                    $db->qn('jobs_count') . ' <= ' . $db->qn('jobs_processed'),
                    $db->qn('aborted') . ' = 1',
                ], 'OR')
        );

        return $this->delete($db->loadColumn());
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
     * @return  string|\Exception
     */
    public function getError($i = null, $toString = true): mixed
    {
        return parent::getError($i, $toString);
    }
}