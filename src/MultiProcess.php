<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types=1);

namespace Wizaplace\Process;

class MultiProcess
{
    public const DEFAULT_CONCURRENCY_THREAD = 5;

    /**
     * @var AsyncProcess[]
     */
    private $asyncProcessCollection = [];

    /**
     * @var AsyncProcess[]
     */
    private $runningProcess = [];

    /**
     * @param AsyncProcess $asyncProcess
     *
     * @return MultiProcess
     */
    public function addAsyncProcess(AsyncProcess $asyncProcess): self
    {
        $this->asyncProcessCollection[] = $asyncProcess;

        return $this;
    }

    /**
     * @return AsyncProcess[]
     */
    public function getAsyncProcessCollection(): array
    {
        return $this->asyncProcessCollection;
    }

    /**
     * @return MultiProcess
     */
    public function resetAsyncProcessCollection() : self
    {
        $this->asyncProcessCollection = [];

        return $this;
    }

    /**
     * @param int $concurrencyThread
     *
     * @return bool
     */
    public function run(int $concurrencyThread = self::DEFAULT_CONCURRENCY_THREAD): bool
    {
        while (false === $this->isFinish()) {
            $this
                ->checkRunningProcess()
                ->startProcess($concurrencyThread)
                ->waiting()
            ;
        }

        return $this->isFailed();
    }

    /**
     * @return MultiProcess
     */
    private function checkRunningProcess(): self
    {
        foreach ($this->runningProcess as $index => $asyncProcess) {
            if ($asyncProcess->isFinished()) {
                unset($this->runningProcess[$index]);
            }
        }

        return $this;
    }

    /**
     * @param int $concurrencyThread
     *
     * @return MultiProcess
     */
    private function startProcess(int $concurrencyThread): self
    {
        foreach ($this->getAsyncProcessCollection() as $asyncProcess) {
            if (false === $asyncProcess->isStarted()
                && count($this->runningProcess) < $concurrencyThread
            ) {
                $this->runningProcess[] = $asyncProcess->start();
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function isFinish(): bool
    {
        if (0 !== count($this->runningProcess)) {
            return false;
        }

        foreach ($this->getAsyncProcessCollection() as $asyncProcess) {
            if (false === $asyncProcess->isFinished()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return MultiProcess
     */
    private function waiting(): self
    {
        if (false === $this->isFinish()) {
            usleep(1000);
        }

        return $this;
    }

    /**
     * @return bool
     */
    private function isFailed(): bool
    {
        foreach ($this->getAsyncProcessCollection() as $asyncProcess) {
            if ($asyncProcess->isFailed()) {
                return true;
            }
        }

        return false;
    }
}
