<?php
/**
 * @package     Pricelabs E4jConnect-VikBooking
 * @author      Giacomo Barlettani
 * @copyright   Copyright (C) 2026 Giacomo Barlettani All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://www.campetroso.it
 */

namespace Joomla\Plugin\Vikchannelmanager\Pricelabs\MVC\Tables;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * E4jConnect Pricelabs queue table.
 *
 * @since 1.0
 */
class QueueTable extends Table
{
    /**
     * @var int
     */
    public int $id = 0;

    /**
     * @var string
     */
    public string $signature = '';

    /**
     * @var string|null
     */
    public ?string $event = null;

    /**
     * @var string
     */
    public string $createdon = '';

    /**
     * @var int
     */
    public int $jobs_count = 0;

    /**
     * @var int
     */
    public int $jobs_processed = 0;

    /**
     * @var int
     */
    public int $aborted = 0;

    /**
     * Class constructor.
     *
     * @param  DatabaseInterface  $db  The database driver instance.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__e4jconnect_pricelabs_queue', 'id', $db);
    }

    /**
     * @inheritDoc
     */
    public function bind($src, $ignore = []): bool
    {
        $src = (array) $src;

        if (empty($src['id']))
        {
            if (empty($src['signature']))
            {
                $src['signature'] = md5(uniqid());
            }

            if (empty($src['createdon']))
            {
                $src['createdon'] = Factory::getDate()->toSql();
            }
        }

        return parent::bind($src, $ignore);
    }
}