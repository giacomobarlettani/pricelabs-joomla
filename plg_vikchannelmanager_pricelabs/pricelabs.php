<?php
/**
 * @package     Pricelabs E4jConnect-VikBooking
 * @author      Giacomo Barlettani
 * @copyright   Copyright (C) 2026 Giacomo Barlettani All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://www.campetroso.it
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Plugin\CMSPlugin;

class PlgVikchannelmanagerPricelabs extends CMSPlugin
{
    public function onValidateAppRequestVikChannelManager($req)
    {
        if (in_array($req, ['pricelabs_set_room_rates', 'pricelabs_async_queue_summary']))
        {
            return true;
        }
        return null;
    }

    public function onAuthoriseAppRequestVikChannelManager($req_type, $email, $role, $action)
    {
        if (!in_array($req_type, ['pricelabs_set_room_rates', 'pricelabs_async_queue_summary']))
        {
            return null;
        }
        return [$role, 'core.admin'];
    }

    public function onPricelabsSetRoomRatesAppRequestVikChannelManager($req_type, $input, $response, $apiUser)
    {
        $container = \Joomla\Plugin\Vikchannelmanager\Pricelabs\Core\Factory::getContainer();
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

    public function onPricelabsAsyncQueueSummaryAppRequestVikChannelManager($req_type, $input, $response, $apiUser)
    {
        $queueId = $input->getString('id_queue');
        if (!$queueId)
        {
            throw new \InvalidArgumentException('Missing queue identifier', 400);
        }
        $queueModel = new \Joomla\Plugin\Vikchannelmanager\Pricelabs\MVC\Models\QueueModel;
        $queue = $queueModel->getSummary($queueId);
        unset($queue->id);
        $response->body = $queue;
    }

    public function onCompletedAppRequestVikChannelManager($req_type, $input, $response, &$statusCode)
    {
        if (!in_array($req_type, ['pricelabs_set_room_rates', 'pricelabs_async_queue_summary']))
        {
            return;
        }
        if ($statusCode !== 200 || is_scalar($response->body))
        {
            return;
        }
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
}

