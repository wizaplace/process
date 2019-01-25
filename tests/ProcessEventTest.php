<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Wizaplace\Process\ProcessEvent as TestedClass;
use Symfony\Component\Process\Process;

class ProcessEventTest extends TestCase
{
    /**
     * @dataProvider getValidEvent
     * @param string $event
     * @param callable $callback
     */
    public function testValidEvent(string $event, callable $callback): void
    {
        $testedClass = new TestedClass($event, $callback);

        $this->assertEquals($event, $testedClass->getEvent());
        $this->assertEquals($callback, $testedClass->getCallback());
    }

    public function testInvalidValidEvent(): void
    {
        $event = uniqid('invalid', true);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid event '.$event);
        new TestedClass(
            $event,
            function () {return 1;}
        );
    }

    /**
     * @dataProvider getCallback
     * @param callable $callback
     * @param Process $process
     * @param $start
     * @param $finish
     * @param $expected
     */
    public function testInvokeCallback(callable $callback, Process $process, $start, $finish, $expected): void
    {
        $testedClass = new TestedClass(TestedClass::EVENT_START, $callback);
        $this->assertEquals($expected, $testedClass->invokeCallback($process, $start, $finish));
    }

    public function getValidEvent(): array
    {
        return [
            [TestedClass::EVENT_FAILED, function () {return 1;}],
            [TestedClass::EVENT_SUCCESS, function () {return 2;}],
            [TestedClass::EVENT_START, function () {return 3;}],
        ];
    }

    public function getCallback(): array
    {
        $process = new Process(uniqid('cmd', true));
        $return = uniqid('return', true);

        return [

            [function (Process $process, $start, $finish) {return null;}, $process, null, null, null],
            [function (Process $process, $start, $finish) use ($return) {return $return;}, $process, null, null, $return],
            [function (Process $process, $start, $finish) {return $start;}, $process, null, null, null],
            [function (Process $process, $start, $finish) {return $finish;}, $process, null, null, null],
            [function (Process $process, $start, $finish) {return $process;}, $process, null, null, $process],
            [function (Process $process, $start, $finish) {return $process->getCommandLine();}, $process, null, null, $process->getCommandLine()],
            [function (Process $process, $start, $finish) {return $start;}, $process, $start = new \DateTimeImmutable(), null, $start],
            [function (Process $process, $start, $finish) {return $finish;}, $process, null, $finish = new \DateTimeImmutable(), $finish],
        ];
    }
}
