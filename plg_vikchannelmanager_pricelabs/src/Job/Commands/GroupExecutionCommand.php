<?php
/**
 * @package     Pricelabs E4jConnect-VikBooking
 * @author      Giacomo Barlettani
 * @copyright   Copyright (C) 2026 Giacomo Barlettani All Rights Reserved.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        https://www.campetroso.it
 */

namespace Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Commands;

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Command;
use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Result;
use Joomla\Plugin\Vikchannelmanager\Pricelabs\Job\Results\JsonResult;

/**
 * Aggregates a list of commands to simultaneously execute them at once.
 *
 * @since 1.0
 */
class GroupExecutionCommand implements Command
{
    /**
     * @var Command[]
     */
    protected array $commands = [];

    /**
     * Class constructor.
     *
     * @param  Command[]  $commands
     */
    public function __construct(array $commands)
    {
        foreach ($commands as $command)
        {
            if ($command instanceof Command)
            {
                $this->commands[] = $command;
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function getSummary(): string
    {
        return sprintf(
            'Executing %d requests.',
            count($this->commands)
        );
    }

    /**
     * @inheritDoc
     */
    public function getExtraData(): string
    {
        $html = [];

        foreach ($this->commands as $i => $command)
        {
            $marginBottom = $i < count($this->commands) - 1 ? 10 : 0;

            $html[] = '<div class="group-exec-wrapper" style="margin-bottom: ' . $marginBottom . 'px;">'
                . '<div class="summary-note"><strong style="font-weight: 500;">' . $command->getSummary() . '</strong></div>'
                . '<div class="extra-note"><small style="font-style: italic;">' . $command->getExtraData() . '</small></div>'
                . '</div>';
        }

        return '<div class="group-exec-command">' . implode("\n", $html) . '</div>';
    }

    /**
     * @inheritDoc
     */
    public function execute(): Result
    {
        $data = [];

        foreach ($this->commands as $command)
        {
            try
            {
                $this->preflight($command);

                $result = $command->execute();

                $this->postflight($command, $result);

                foreach ($result->jsonSerialize() as $k => $v)
                {
                    if (in_array($k, ['success', 'warnings', 'errors']))
                    {
                        $data[$k] = array_values(array_unique(array_merge($data[$k] ?? [], $v)));
                    }
                    else
                    {
                        $data[$k] = $v;
                    }
                }
            }
            catch (\Exception $e)
            {
                $data['warnings'] = array_merge($data['warnings'] ?? [], [$e->getMessage()]);
            }
        }

        return new JsonResult($data);
    }

    /**
     * Called before executing each command.
     * Children classes can override this to perform additional actions.
     *
     * @param   Command  $command
     *
     * @return  void
     *
     * @since   1.0
     */
    protected function preflight(Command $command): void
    {
        // do nothing
    }

    /**
     * Called after executing each command.
     * Children classes can override this to perform additional actions.
     *
     * @param   Command  $command
     * @param   Result   $result
     *
     * @return  void
     *
     * @since   1.0
     */
    protected function postflight(Command $command, Result $result): void
    {
        // do nothing
    }
}