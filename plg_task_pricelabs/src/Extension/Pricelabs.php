<?php
/**
 * @package     Pricelabs E4jConnect-VikBooking
 * @author      Giacomo Barlettani
 * @copyright   Copyright (C) 2026 Giacomo Barlettani All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://www.campetroso.it
 */

namespace Joomla\Plugin\Task\Pricelabs\Extension;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Scheduler\Administrator\Event\ExecuteTaskEvent;
use Joomla\Component\Scheduler\Administrator\Task\Status;
use Joomla\Component\Scheduler\Administrator\Traits\TaskPluginTrait;
use Joomla\Event\SubscriberInterface;

/**
 * Task plugin extension class for Pricelabs.
 *
 * @since 1.0
 */
class Pricelabs extends CMSPlugin implements SubscriberInterface
{
    use TaskPluginTrait;

    /**
     * @var string[]
     */
    protected const TASKS_MAP = [
        'pricelabs.processQueue' => [
            'langConstPrefix' => 'PLG_TASK_PRICELABS_PROCESS_QUEUE',
            'method'          => 'processQueue',
            'form'            => 'process_queue',
        ],
        'pricelabs.flushQueue' => [
            'langConstPrefix' => 'PLG_TASK_PRICELABS_FLUSH_QUEUE',
            'method'          => 'flushQueue',
            'form'            => 'flush_queue',
        ],
    ];

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onTaskOptionsList'    => 'advertiseRoutines',
            'onExecuteTask'        => 'standardRoutineHandler',
            'onContentPrepareForm' => 'enhanceTaskItemForm',
        ];
    }

    /**
     * Processes the pending jobs queue.
     *
     * @param   ExecuteTaskEvent  $event
     *
     * @return  int
     */
    protected function processQueue(ExecuteTaskEvent $event): int
    {
        if (!$this->isPricelabsPluginActive())
        {
            $this->logTask('Pricelabs VikChannelManager plugin is not active.');
            return Status::KNOCKOUT;
        }

        try
        {
            $processor = \Joomla\Plugin\Vikchannelmanager\Pricelabs\Core\Factory::getContainer()
                ->get('job.processor');

            // legge max_jobs dal form del task, default 6
            $maxJobs = (int) ($event->getArgument('params')->max_jobs ?? 6);
            $processor->run($maxJobs);
        }
        catch (\Throwable $e)
        {
            $this->logTask(
                sprintf('Error processing queue: %s', $e->getMessage()),
                'error'
            );

            return Status::KNOCKOUT;
        }

        $this->logTask('Queue processed successfully.');

        return Status::OK;
    }

    /**
     * Flushes completed queues older than 1 month.
     *
     * @param   ExecuteTaskEvent  $event
     *
     * @return  int
     */
    protected function flushQueue(ExecuteTaskEvent $event): int
    {
        if (!$this->isPricelabsPluginActive())
        {
            $this->logTask('Pricelabs VikChannelManager plugin is not active.');
            return Status::KNOCKOUT;
        }

        try
        {
            $queueModel = new \Joomla\Plugin\Vikchannelmanager\Pricelabs\MVC\Models\QueueModel;

            // legge threshold dal form del task, default -1 month
            $threshold = $event->getArgument('params')->threshold ?? '-1 month';
            $queueModel->flush($threshold);
        }
        catch (\Throwable $e)
        {
            $this->logTask(
                sprintf('Error flushing queue: %s', $e->getMessage()),
                'error'
            );

            return Status::KNOCKOUT;
        }

        $this->logTask('Queue flushed successfully.');

        return Status::OK;
    }

    /**
     * Checks whether the Pricelabs VikChannelManager plugin is active.
     *
     * @return  bool
     */
    protected function isPricelabsPluginActive(): bool
    {
        $db = \Joomla\CMS\Factory::getDbo();

        $db->setQuery(
            $db->getQuery(true)
                ->select($db->qn('enabled'))
                ->from($db->qn('#__extensions'))
                ->where($db->qn('type') . ' = ' . $db->q('plugin'))
                ->where($db->qn('folder') . ' = ' . $db->q('vikchannelmanager'))
                ->where($db->qn('element') . ' = ' . $db->q('pricelabs'))
        );

        return (bool) $db->loadResult();
    }
}