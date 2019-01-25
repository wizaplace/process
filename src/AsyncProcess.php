<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types=1);

namespace Wizaplace\Process;

use Symfony\Component\Process\Process;

class AsyncProcess
{
    /**
     * @var Process
     */
    private $process;

    /**
     * @var ProcessEvent[]
     */
    private $processEvents = [];

    /**
     * @var \DateTimeImmutable|null
     */
    private $startTime;

    /**
     * @var \DateTimeImmutable|null
     */
    private $finishTime;

    /**
     * @var bool
     */
    private $isStarted = false;

    /**
     * @var bool
     */
    private $isFinish = false;

    /**
     * @var bool
     */
    private $isFailed = false;

    /**
     * @param Process $process
     */
    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    /**
     * @return Process
     */
    public function getProcess(): Process
    {
        return $this->process;
    }

    /**
     * @param ProcessEvent $callback
     *
     * @return AsyncProcess
     */
    public function addProcessEvent(ProcessEvent $callback): self
    {
        $this->processEvents[] = $callback;

        return $this;
    }

    /**
     * @return ProcessEvent[]
     */
    public function getProcessEvents(): array
    {
        return $this->processEvents;
    }

    /**
     * @param null|\DateTimeImmutable $startTime
     *
     * @return AsyncProcess
     *
     * @throws \RuntimeException
     */
    public function start(?\DateTimeImmutable $startTime = null): self
    {
        if ($this->isStarted) {
            throw new \RuntimeException('The process is already started');
        }

        $this->isStarted = true;
        $this->startTime = $startTime ?? new \DateTimeImmutable();
        $this
            ->notifyEvent(ProcessEvent::EVENT_START)
            ->getProcess()->start()
        ;

        return $this;
    }

    /**
     * @return bool
     */
    public function isStarted(): bool
    {
        return $this->isStarted;
    }

    /**
     * @param null|\DateTimeImmutable $finishTime
     *
     * @return bool
     */
    public function isFinished(?\DateTimeImmutable $finishTime = null): bool
    {
        if (false === $this->isFinish) {
            if ($this->getProcess()->isTerminated()) {
                $this->isFinish = true;
                $this->finishTime = $finishTime ?? new \DateTimeImmutable();

                if (0 === $this->getProcess()->getExitCode()) {
                    $this->notifyEvent(ProcessEvent::EVENT_SUCCESS);
                } else {
                    $this->isFailed = true;
                    $this->notifyEvent(ProcessEvent::EVENT_FAILED);
                }
            }
        }

        return $this->isFinish;
    }

    /**
     * @return bool
     */
    public function isFailed(): bool
    {
        return $this->isFailed;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getStartTime(): ?\DateTimeImmutable
    {
        return $this->startTime;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getFinishTime(): ?\DateTimeImmutable
    {
        return $this->finishTime;
    }

    /**
     * @param string $event
     *
     *  @return AsyncProcess
     */
    private function notifyEvent(string $event): self
    {
        foreach ($this->getProcessEvents() as $processEvents) {
            if ($event === $processEvents->getEvent()) {
                $processEvents->invokeCallback(
                    $this->getProcess(),
                    $this->getStartTime(),
                    $this->getFinishTime()
                );
            }
        }

        return $this;
    }
}
