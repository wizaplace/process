<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Wizaplace\Process\AsyncProcess as TestedClass;
use Wizaplace\Process\ProcessEvent;

class AsyncProcessTest extends TestCase
{
    public function testConstruct(): void
    {
        $testedClass = new TestedClass($process = new Process([uniqid('cmd', true)]));

        $this->assertEquals($process, $testedClass->getProcess());
        $this->assertEquals(false, $testedClass->isStarted());
        $this->assertEquals(false, $testedClass->isFinished());
        $this->assertEquals(false, $testedClass->isFailed());
    }

    /**
     * @dataProvider getProcessEvent
     * @param array $processEvents
     */
    public function testAddProcessEvent(array $processEvents): void
    {
        $testedClass = new TestedClass($process = new Process([uniqid('cmd', true)]));

        foreach ($processEvents as $processEvent) {
            $this->assertEquals($testedClass, $testedClass->addProcessEvent($processEvent));
        }

        $this->assertEquals($processEvents, $testedClass->getProcessEvents());
    }

    /**
     * @dataProvider getDateTimeImmutable
     * @param \DateTimeImmutable $dateTime
     */
    public function testStart(?\DateTimeImmutable $dateTime): void
    {
        $mockProcess = $this->createMock(Process::class);
        $mockProcess
            ->method('start')
            ->willReturn(null);
        ;

        $mockProcess
            ->expects($this->once())
            ->method('start')
        ;

        $mockProcessEventStart = $this->createMock(ProcessEvent::class);
        $mockProcessEventStart
            ->method('getEvent')
            ->willReturn(ProcessEvent::EVENT_START);
        ;

        $mockProcessEventStart
            ->expects($this->once())
            ->method('getEvent')
        ;

        $mockProcessEventStart
            ->method('invokeCallback')
            ->willReturn(null);
        ;

        if ($dateTime instanceof \DateTimeImmutable) {
            $mockProcessEventStart
                ->expects($this->once())
                ->method('invokeCallback')
                ->with($mockProcess, $dateTime, null)
            ;
        } else {
            $mockProcessEventStart
                ->expects($this->once())
                ->method('invokeCallback')
            ;
        }

        $mockProcessEventSuccess = $this->createMock(ProcessEvent::class);
        $mockProcessEventSuccess
            ->method('getEvent')
            ->willReturn(ProcessEvent::EVENT_SUCCESS);
        ;

        $mockProcessEventSuccess
            ->expects($this->once())
            ->method('getEvent')
        ;


        $mockProcessEventSuccess
            ->expects($this->never())
            ->method('invokeCallback')
        ;

        $mockProcessEventFailed = $this->createMock(ProcessEvent::class);
        $mockProcessEventFailed
            ->method('getEvent')
            ->willReturn(ProcessEvent::EVENT_FAILED);
        ;

        $mockProcessEventFailed
            ->expects($this->once())
            ->method('getEvent')
        ;

        $mockProcessEventFailed
            ->expects($this->never())
            ->method('invokeCallback')
        ;

        $testedClass = new TestedClass($mockProcess);
        $testedClass
            ->addProcessEvent($mockProcessEventStart)
            ->addProcessEvent($mockProcessEventSuccess)
            ->addProcessEvent($mockProcessEventFailed)
        ;

        $this->assertEquals(false, $testedClass->isStarted());
        $this->assertEquals($testedClass, $testedClass->start($dateTime));
        $this->assertEquals(true, $testedClass->isStarted());
        $this->assertInstanceOf(\DateTimeImmutable::class, $testedClass->getStartTime());

        if ($dateTime instanceof \DateTimeImmutable) {
            $this->assertEquals($dateTime, $testedClass->getStartTime());
        }

        $this->assertEquals(null, $testedClass->getFinishTime());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The process is already started');

        $testedClass->start();
    }

    /**
     * @dataProvider getDateTimeImmutable
     * @param null|\DateTimeImmutable $dateTime
     * @param bool $failedProcessRules
     */
    public function testFinishSuccess(?\DateTimeImmutable $dateTime, bool $failedProcessRules = false): void
    {
        $mockProcess = $this->createMock(Process::class);
        $mockProcess
            ->method('isTerminated')
            ->willReturnOnConsecutiveCalls(false, false, false, true);
        ;

        $mockProcess
            ->method('getExitCode')
            ->willReturn(0);
        ;

        $mockProcess
            ->expects($this->exactly(4))
            ->method('isTerminated')
        ;

        $mockProcess
            ->expects($this->once())
            ->method('getExitCode')
        ;

        $mockProcessEventStart = $this->createMock(ProcessEvent::class);
        $mockProcessEventStart
            ->method('getEvent')
            ->willReturn(ProcessEvent::EVENT_START);
        ;

        $mockProcessEventStart
            ->expects($this->once())
            ->method('getEvent')
        ;

        $mockProcessEventStart
            ->expects($this->never())
            ->method('invokeCallback')
        ;

        $mockProcessEventSuccess = $this->createMock(ProcessEvent::class);
        $mockProcessEventSuccess
            ->method('getEvent')
            ->willReturn(ProcessEvent::EVENT_SUCCESS);
        ;

        $mockProcessEventSuccess
            ->expects($this->once())
            ->method('getEvent')
        ;

        if ($dateTime instanceof \DateTimeImmutable) {
            $mockProcessEventSuccess
                ->expects($this->once())
                ->method('invokeCallback')
                ->with($mockProcess, null, $dateTime)
            ;
        } else {
            $mockProcessEventSuccess
                ->expects($this->once())
                ->method('invokeCallback')
            ;
        }

        $mockProcessEventFailed = $this->createMock(ProcessEvent::class);
        $mockProcessEventFailed
            ->method('getEvent')
            ->willReturn(ProcessEvent::EVENT_FAILED);
        ;

        $mockProcessEventFailed
            ->expects($this->once())
            ->method('getEvent')
        ;

        $mockProcessEventFailed
            ->expects($this->never())
            ->method('invokeCallback')
        ;

        $testedClass = new TestedClass($mockProcess);
        if (true === $failedProcessRules) {
            $testedClass = new TestedClass($mockProcess, function () {return false;});
        }

        $testedClass
            ->addProcessEvent($mockProcessEventStart)
            ->addProcessEvent($mockProcessEventSuccess)
            ->addProcessEvent($mockProcessEventFailed)
        ;

        $this->assertEquals(null, $testedClass->getFinishTime());
        $this->assertEquals(false, $testedClass->isFinished($dateTime));
        $this->assertEquals(false, $testedClass->isFinished($dateTime));
        $this->assertEquals(false, $testedClass->isFinished($dateTime));
        $this->assertEquals(true, $testedClass->isFinished($dateTime));
        $this->assertEquals(true, $testedClass->isFinished($dateTime));
        $this->assertEquals(true, $testedClass->isFinished($dateTime));
        $this->assertInstanceOf(\DateTimeImmutable::class, $testedClass->getFinishTime());

        if ($dateTime instanceof \DateTimeImmutable) {
            $this->assertEquals($dateTime, $testedClass->getFinishTime());
        }
    }

    /**
     * @dataProvider getDateTimeImmutable
     * @param null|\DateTimeImmutable $dateTime
     * @param bool $failedProcessRules
     */
    public function testIsFinishFailed(?\DateTimeImmutable $dateTime, bool $failedProcessRules = false): void
    {
        $mockProcess = $this->createMock(Process::class);
        $mockProcess
            ->method('isTerminated')
            ->willReturnOnConsecutiveCalls(false, false, false, true);
        ;

        if (false === $failedProcessRules) {
            $mockProcess
                ->method('getExitCode')
                ->willReturn(random_int(1, 255))
            ;
        } else {
            $mockProcess
                ->method('getExitCode')
                ->willReturn(0)
            ;
        }

        $mockProcess
            ->expects($this->exactly(4))
            ->method('isTerminated')
        ;

        $mockProcess
            ->expects($this->once())
            ->method('getExitCode')
        ;

        $mockProcessEventStart = $this->createMock(ProcessEvent::class);
        $mockProcessEventStart
            ->method('getEvent')
            ->willReturn(ProcessEvent::EVENT_START);
        ;

        $mockProcessEventStart
            ->expects($this->once())
            ->method('getEvent')
        ;

        $mockProcessEventStart
            ->expects($this->never())
            ->method('invokeCallback')
        ;

        $mockProcessEventSuccess = $this->createMock(ProcessEvent::class);

        $mockProcessEventSuccess
            ->method('getEvent')
            ->willReturn(ProcessEvent::EVENT_SUCCESS);
        ;

        $mockProcessEventSuccess
            ->expects($this->once())
            ->method('getEvent')
        ;

        $mockProcessEventSuccess
            ->expects($this->never())
            ->method('invokeCallback')
        ;

        $mockProcessEventFailed = $this->createMock(ProcessEvent::class);
        $mockProcessEventFailed
            ->method('getEvent')
            ->willReturn(ProcessEvent::EVENT_FAILED);
        ;

        $mockProcessEventFailed
            ->method('invokeCallback')
            ->willReturn(null);
        ;

        $mockProcessEventFailed
            ->expects($this->once())
            ->method('getEvent')
        ;

        if ($dateTime instanceof \DateTimeImmutable) {
            $mockProcessEventFailed
                ->expects($this->once())
                ->method('invokeCallback')
                ->with($mockProcess, null, $dateTime)
            ;
        } else {
            $mockProcessEventFailed
                ->expects($this->once())
                ->method('invokeCallback')
            ;
        }

        $testedClass = new TestedClass($mockProcess);
        if (true === $failedProcessRules) {
            $testedClass = new TestedClass($mockProcess, function () {return true;});
        }

        $testedClass
            ->addProcessEvent($mockProcessEventStart)
            ->addProcessEvent($mockProcessEventSuccess)
            ->addProcessEvent($mockProcessEventFailed)
        ;


        $this->assertEquals(null, $testedClass->getFinishTime());
        $this->assertEquals(false, $testedClass->isFinished($dateTime));
        $this->assertEquals(false, $testedClass->isFinished($dateTime));
        $this->assertEquals(false, $testedClass->isFinished($dateTime));
        $this->assertEquals(true, $testedClass->isFinished($dateTime));
        $this->assertEquals(true, $testedClass->isFinished($dateTime));
        $this->assertEquals(true, $testedClass->isFinished($dateTime));
        $this->assertEquals(true, $testedClass->isFailed());
        $this->assertInstanceOf(\DateTimeImmutable::class, $testedClass->getFinishTime());

        if ($dateTime instanceof \DateTimeImmutable) {
            $this->assertEquals($dateTime, $testedClass->getFinishTime());
        }
    }

    public function getProcessEvent(): array
    {
        $event1 = new ProcessEvent(ProcessEvent::EVENT_START, function () {});
        $event2 = new ProcessEvent(ProcessEvent::EVENT_FAILED, function () {});
        $event3 = new ProcessEvent(ProcessEvent::EVENT_FAILED, function () {});
        $event4 = new ProcessEvent(ProcessEvent::EVENT_SUCCESS, function () {});

        return [
            [[$event1]],
            [[$event1, $event2]],
            [[$event1, $event3]],
            [[$event1, $event2, $event3, $event4]],
            [[$event4, $event3, $event2, $event1]],
        ];
    }

    public function getDateTimeImmutable(): array
    {
        return [
            [null],
            [new \DateTimeImmutable()],
            [new \DateTimeImmutable('2019-01-21')],
            [new \DateTimeImmutable('2018-12-31')],
            [new \DateTimeImmutable('2020-02-29')],
            [null, true],
            [new \DateTimeImmutable(), true],
            [new \DateTimeImmutable('2019-01-21'), true],
            [new \DateTimeImmutable('2018-12-31'), true],
            [new \DateTimeImmutable('2020-02-29'), true],
        ];
    }
}
