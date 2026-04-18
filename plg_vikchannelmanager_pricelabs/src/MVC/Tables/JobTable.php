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
 * E4jConnect Pricelabs jobs table.
 *
 * @since 1.0
 */
class JobTable extends Table
{
    /**
     * @var int
     */
    public int $id = 0;

    /**
     * @var int|null
     */
    public ?int $id_queue = null;

    /**
     * @var string
     */
    public string $createdon = '';

    /**
     * @var string|null
     */
    public ?string $command = null;

    /**
     * @var string|null
     */
    public ?string $results = null;

    /**
     * @var string|null
     */
    public ?string $executedon = null;

    /**
     * @var string|null
     */
    public ?string $completedon = null;

    /**
     * @var int|null
     */
    public ?int $status = null;

    /**
     * Class constructor.
     *
     * @param  DatabaseInterface  $db  The database driver instance.
     */
    public function __construct(DatabaseInterface $db)
    {
        parent::__construct('#__e4jconnect_pricelabs_jobs', 'id', $db);
    }

    /**
     * @inheritDoc
     */
    public function bind($src, $ignore = []): bool
    {
        $src = (array) $src;

        if (empty($src['id']))
        {
            if (empty($src['createdon']))
            {
                $src['createdon'] = Factory::getDate()->toSql();
            }
        }

        if (!empty($src['command']) && is_object($src['command']))
        {
            $src['command'] = serialize($src['command']);
        }

        if (!empty($src['results']) && !is_scalar($src['results']))
        {
            $src['results'] = json_encode($src['results']);
        }

        return parent::bind($src, $ignore);
    }
}