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

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

/**
 * Pricelabs E4jConnect-VikBooking plugin for Joomla.
 * Bridges PriceLabs with VikBooking via the VikChannelManager API framework.
 *
 * @since 1.0
 */
class PlgVikchannelmanagerPricelabs extends CMSPlugin implements SubscriberInterface
{
    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onValidateAppRequestVikChannelManager'   => 'onValidateAppRequestVikChannelManager',
            'onAuthoriseAppRequestVikChannelManager'  => 'onAuthoriseAppRequestVikChannelManager',
            'onCompletedAppRequestVikChannelManager'  => 'onCompletedAppRequestVikChannelManager',
            'onPricelabsSetRoomRatesAppRequestVikChannelManager'      => 'onPricelabsSetRoomRatesAppRequestVikChannelManager',
            'onPricelabsAsyncQueueSummaryAppRequestVikChannelManager' => 'onPricelabsAsyncQueueSummaryAppRequestVikChannelManager',
        ];
    }

    /**
     * Validates whether the incoming request type is handled by this plugin.
     * Equivalent of WordPress filter: vikchannelmanager_validate_app_request
     *
     * @param   Event  $event
     *
     * @return  void
     *
     * @since   1.0
     */
    public function onValidateAppRequestVikChannelManager(Event $event): void
    {
        [$req] = array_values($event->getArguments());

        if (in_array($req, ['pricelabs_set_room_rates', 'pricelabs_async_queue_summary']))
        {
            $event->setArgument('result', true);
        }
    }

    /**
     * Authorises the request for the current App account.
     * Equivalent of WordPress filter: vikchannelmanager_authorise_app_request
     *
     * @param   Event  $event
     *
     * @return  void
     *
     * @since   1.0
     */
    public function onAuthoriseAppRequestVikChannelManager(Event $event): void
    {
        [$req_type, $email, $role, $action] = array_values($event->getArguments());

        if (!in_array($req_type, ['pricelabs_set_room_rates', 'pricelabs_async_queue_summary']))
        {
            return;
        }

        // require administrator level for both endpoints
        $event->setArgument('result', [$role, 'core.admin']);
    }

    /**
     * Executes the pricelabs_set_room_rates request.
     * Equivalent of WordPress action: vikchannelmanager_pricelabs_set_room_rates_app_request
     *
     * @param   Event  $event
     *
     * @return  void
     *
     * @since   1.0
     */
    public function onPricelabsSetRoomRatesAppRequestVikChannelManager(Event $event): void
    {
        [$req_type, $input, $response, $apiUser] = array_values($event->getArguments());

        $container = $this->getContainer();

        /** @var \Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Processor */
        $processor = $container->get('job.processor');

        $builder = new \Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Builders\RARLazyBuilder($apiUser);

        foreach ($builder->getCommandsFromRequest($input) as $command)
        {
            $processor->register($command);
        }

        $response->body = [
            'id_queue' => $processor->getQueueID(),
        ];
    }

    /**
     * Executes the pricelabs_async_queue_summary request.
     * Equivalent of WordPress action: vikchannelmanager_pricelabs_async_queue_summary_app_request
     *
     * @param   Event  $event
     *
     * @return  void
     *
     * @since   1.0
     */
    public function onPricelabsAsyncQueueSummaryAppRequestVikChannelManager(Event $event): void
    {
        [$req_type, $input, $response, $apiUser] = array_values($event->getArguments());

        $queueId = $input->getString('id_queue');

        if (!$queueId)
        {
            throw new \InvalidArgumentException('Missing queue identifier', 400);
        }

        $queueModel = new \Joomla\Plugin\Vikchannelmanager\Pricelabs\MVC\Models\QueueModel;

        $queue = $queueModel->getSummary($queueId);

        // unset unneeded properties
        unset($queue->id);

        $response->body = $queue;
    }

    /**
     * Manipulates the response object after the request has been completed.
     * Equivalent of WordPress action: vikchannelmanager_completed_app_request
     *
     * @param   Event  $event
     *
     * @return  void
     *
     * @since   1.0
     */
    public function onCompletedAppRequestVikChannelManager(Event $event): void
    {
        [$req_type, $input, $response, $statusCode] = array_values($event->getArguments());

        if (!in_array($req_type, ['pricelabs_set_room_rates', 'pricelabs_async_queue_summary']))
        {
            return;
        }

        if ($statusCode !== 200 || is_scalar($response->body))
        {
            return;
        }

        // flatten the response body into the response object
        $body = $response->body;

        foreach ($response as $prop => $data)
        {
            unset($response->$prop);
        }

        foreach ($body as $prop => $data)
        {
            $response->$prop = $data;
        }
    }

    /**
     * Returns the DI container for this plugin.
     *
     * @return  \Joomla\DI\Container
     *
     * @since   1.0
     */
    protected function getContainer(): \Joomla\DI\Container
    {
        return \Joomla\Plugin\Vikchannelmanager\Pricelabs\Core\Factory::getContainer();
    }
}