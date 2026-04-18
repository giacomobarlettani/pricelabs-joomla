<?php
/**
 * @package     Pricelabs E4jConnect-VikBooking
 * @author      Giacomo Barlettani
 * @copyright   Copyright (C) 2026 Giacomo Barlettani All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://www.campetroso.it
 */

namespace Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Collections;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Collection;
use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Command;
use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Result;
use Joomla\Plugin\Vikchannelmanager\Pricelabs\MVC\Models\QueueModel;
use Joomla\Plugin\Vikchannelmanager\Pricelabs\MVC\Models\JobModel;

/**
 * E4jConnect Pricelabs DatabaseCollection implementation.
 *
 * @since 1.0
 */
class DatabaseCollection implements Collection
{
    /**
     * @var object[]
     */
    protected array $queue = [];

    /**
     * @var int
     */
    protected int $queueId = 0;

    /**
     * @var QueueModel
     */
    protected QueueModel $queueModel;

    /**
     * @var JobModel
     */
    protected JobModel $jobModel;

    /**
     * Class constructor will initialize the needed models.
     */
    public function __construct()
    {
        $this->queueModel = new QueueModel;
        $this->jobModel   = new JobModel;
    }

    /**
     * @inheritDoc
     */
    public function createQueue(string $id, ?string $event = null): void
    {
        $data = [
            'signature' => $id,
            'event'     => $event,
        ];

        $this->queueId = $this->queueModel->save($data);

        if (!$this->queueId)
        {
            $error = $this->queueModel->getError();

            if (!$error instanceof \Exception)
            {
                $error = new \RuntimeException($error ?: 'An error has occurred while creating the jobs queue.', 500);
            }

            throw $error;
        }
    }

    /**
     * @inheritDoc
     */
    public function push(Command $command): void
    {
        $data = [
            'id_queue' => $this->queueId,
            'command'  => $command,
        ];

        $id = $this->jobModel->save($data);

        if (!$id)
        {
            $error = $this->jobModel->getError();

            if (!$error instanceof \Exception)
            {
                $error = new \RuntimeException($error ?: 'An error has occurred while scheduling the job.', 500);
            }

            throw $error;
        }

        $queueItem = $this->queueModel->getItem($this->queueId);

        $this->queueModel->save([
            'id'         => $this->queueId,
            'jobs_count' => $queueItem->jobs_count + 1,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function pull(): ?Command
    {
        $job = $this->jobModel->getNextJob();

        if (!$job)
        {
            return null;
        }

        $this->queue[] = $job;

        $this->jobModel->save([
            'id'         => $job->id,
            'executedon' => \Joomla\CMS\Factory::getDate()->toSql(),
        ]);

        return $job->command;
    }

    /**
     * @inheritDoc
     */
    public function complete(Command $command, Result $result): void
    {
        $index = $this->findJob($command);

        if ($index === null)
        {
            return;
        }

        $job = array_splice($this->queue, $index, 1)[0];

        $resultStatus = 1;

        if ($result->getErrors())
        {
            $resultStatus = 0;
        }
        elseif ($result->getWarnings())
        {
            $resultStatus = 2;
        }

        $this->jobModel->save([
            'id'          => $job->id,
            'status'      => $resultStatus,
            'results'     => $result,
            'completedon' => \Joomla\CMS\Factory::getDate()->toSql(),
        ]);

        if ($job->id_queue)
        {
            $queueItem = $this->queueModel->getItem($job->id_queue);

            $this->queueModel->save([
                'id'             => $job->id_queue,
                'jobs_processed' => $queueItem->jobs_processed + 1,
            ]);
        }
    }

    /**
     * Finds the job scheduled by the provided command.
     *
     * @param   Command  $command
     *
     * @return  int|null
     */
    protected function findJob(Command $command): ?int
    {
        foreach ($this->queue as $i => $job)
        {
            if ($job->command === $command)
            {
                return $i;
            }
        }

        return null;
    }
}