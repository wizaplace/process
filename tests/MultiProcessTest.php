<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Wizaplace\Process\MultiProcess as TestedClass;
use Wizaplace\Process\AsyncProcess;

class MultiProcessTest extends TestCase
{
    public function testConstant(): void
    {
        $this->assertEquals(5, TestedClass::DEFAULT_CONCURRENCY_THREAD);
    }

    /**
     * @dataProvider getAsyncProcess
     * @param array $asyncProcessCollection
     */
    public function testAsyncProcess(array $asyncProcessCollection): void
    {
        $testedClass = new TestedClass();

        foreach ($asyncProcessCollection as $asyncProcess) {
            $this->assertEquals($testedClass, $testedClass->addAsyncProcess($asyncProcess));
        }

        $this->assertEquals($asyncProcessCollection, $testedClass->getAsyncProcessCollection());
        $this->assertEquals($testedClass, $testedClass->resetAsyncProcessCollection());
        $this->assertEquals([], $testedClass->getAsyncProcessCollection());
    }

    /**
     * @dataProvider getMockAsyncProcess
     * @param array $asyncProcessCollection
     * @param bool $failed
     */
    public function testRunSuccess(array $asyncProcessCollection, bool $failed): void
    {
        $testedClass = new TestedClass();
        foreach ($asyncProcessCollection as $asyncProcess) {
            $testedClass->addAsyncProcess($asyncProcess);
        }

        $this->assertEquals($failed, $testedClass->run());
    }

    public function getMockAsyncProcess(): array
    {
        return [
            [[$this->createMockAsyncProcess(false)], false],
            [[$this->createMockAsyncProcess(true)], true],
        ];
    }

    private function createMockAsyncProcess(bool $failed): AsyncProcess
    {
        $mockAsyncProcess = $this->createMock(AsyncProcess::class);
        $mockAsyncProcess
            ->method('isStarted')
            ->willReturnOnConsecutiveCalls(false, true)
        ;

        $mockAsyncProcess
            ->expects($this->exactly(2))
            ->method('isStarted')
        ;

        $mockAsyncProcess
            ->method('start')
            ->willReturn($mockAsyncProcess)
        ;

        $mockAsyncProcess
            ->expects($this->once())
            ->method('start')
        ;

        $mockAsyncProcess
            ->method('isFinished')
            ->willReturnOnConsecutiveCalls(false, true, true, true)
        ;

        $mockAsyncProcess
            ->expects($this->exactly(4))
            ->method('isFinished')
        ;

        $mockAsyncProcess
            ->method('isFailed')
            ->willReturn($failed)
        ;

        $mockAsyncProcess
            ->expects($this->once())
            ->method('isFailed')
        ;

        return $mockAsyncProcess;
    }

    public function getAsyncProcess(): array
    {
        $asyncProcess1 = new AsyncProcess(new Process([uniqid('cmd', true)]));
        $asyncProcess2 = new AsyncProcess(new Process([uniqid('cmd', true)]));
        $asyncProcess3 = new AsyncProcess(new Process([uniqid('cmd', true)]));
        $asyncProcess4 = new AsyncProcess(new Process([uniqid('cmd', true)]));

        return [
            [[$asyncProcess1]],
            [[$asyncProcess1, $asyncProcess2]],
            [[$asyncProcess1, $asyncProcess3]],
            [[$asyncProcess1, $asyncProcess2, $asyncProcess3, $asyncProcess4]],
            [[$asyncProcess4, $asyncProcess3, $asyncProcess2, $asyncProcess1]],
        ];
    }
}
