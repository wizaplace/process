<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types=1);

namespace Wizaplace\Process;

use Symfony\Component\Process\Process;

class ProcessEvent
{
    public const EVENT_START = 'start';
    public const EVENT_SUCCESS = 'success';
    public const EVENT_FAILED = 'failed';

    /**
     * @var string
     */
    private $event;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @param string $event
     * @param callable $callback
     */
    public function __construct(string $event, callable $callback)
    {
        if (false === in_array($event, [self::EVENT_START, self::EVENT_SUCCESS, self::EVENT_FAILED])) {
            throw new \InvalidArgumentException(sprintf(
                "Invalid event %s",
                $event
            ));
        }

        $this->event = $event;
        $this->callback = $callback;
    }

    /**
     * @return string
     */
    public function getEvent(): string
    {
        return $this->event;
    }

    /**
     * @return callable
     */
    public function getCallback(): callable
    {
        return $this->callback;
    }

    /**
     * @param Process $process
     * @param \DateTimeInterface $startTime
     * @param \DateTimeInterface $finishTime
     *
     * @return callable
     */
    public function invokeCallback(
        Process $process,
        ?\DateTimeInterface $startTime,
        ?\DateTimeInterface $finishTime
    ) {
        return call_user_func_array($this->callback, [$process, $startTime, $finishTime]);
    }
}
