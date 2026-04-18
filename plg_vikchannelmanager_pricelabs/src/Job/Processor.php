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
 * E4jConnect Pricelabs Processor implementation.
 *
 * @since 1.0
 */
class Processor
{
    /**
     * @var Collection
     */
    protected Collection $collection;

    /**
     * @var string|null
     */
    protected ?string $queueId = null;

    /**
     * Class constructor.
     *
     * @param  Collection  $collection  The interface used to write/read the jobs.
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Registers the command for a queue.
     *
     * @param   Command  $command
     *
     * @return  self
     */
    public function register(Command $command): self
    {
        if (!$this->queueId)
        {
            $this->queueId = md5(uniqid());

            $event = basename(str_replace('\\', '/', strtolower(get_class($command))));

            $this->collection->createQueue($this->queueId, $event);
        }

        $this->collection->push($command);

        return $this;
    }

    /**
     * Processes the pending jobs.
     *
     * @param   int  $max  Maximum number of jobs to execute per flow.
     *
     * @return  void
     */
    public function run(int $max = 5): void
    {
        $i = 0;

        while ((++$i <= $max || !$max) && ($command = $this->collection->pull()))
        {
            try
            {
                /** @var Result */
                $result = $command->execute();
            }
            catch (\Throwable $error)
            {
                $result = new \Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Results\JsonResult([
                    'errors' => [
                        $error->getMessage(),
                    ],
                ]);
            }

            $this->collection->complete($command, $result);
        }
    }

    /**
     * Gets the current queue signature ID.
     *
     * @return  string|null
     */
    public function getQueueID(): ?string
    {
        return $this->queueId;
    }
}