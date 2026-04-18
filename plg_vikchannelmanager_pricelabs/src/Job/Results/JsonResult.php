<?php
/**
 * @package     Pricelabs E4jConnect-VikBooking
 * @author      Giacomo Barlettani
 * @copyright   Copyright (C) 2026 Giacomo Barlettani All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://www.campetroso.it
 */

namespace Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Results;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Result;

/**
 * E4jConnect Pricelabs JsonResult implementation.
 *
 * @since 1.0
 */
class JsonResult implements Result, \JsonSerializable
{
    /**
     * @var object
     */
    protected object $json;

    /**
     * Class constructor.
     *
     * @param  mixed  $json  Either a JSON string or an array|object of data.
     */
    public function __construct(mixed $json)
    {
        if (is_string($json))
        {
            $json = json_decode($json);
        }

        $this->json = (object) $json;
    }

    /**
     * @inheritDoc
     */
    public function getSuccesses(): array
    {
        return (array) ($this->json->success ?? []);
    }

    /**
     * @inheritDoc
     */
    public function getErrors(): array
    {
        return (array) ($this->json->errors ?? []);
    }

    /**
     * @inheritDoc
     */
    public function getWarnings(): array
    {
        return (array) ($this->json->warnings ?? []);
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize(): mixed
    {
        return $this->json;
    }
}