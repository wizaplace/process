# Process

## Installation

````
composer require wizaplace/process
````

## Usage

````
use Wizaplace\Process\AsyncProcess;
use Wizaplace\Process\MultiProcess;
use Wizaplace\Process\ProcessEvent;
use Symfony\Component\Process\Process;

$successCallback = function (Process $process, $startTime, $finishTime) {
    echo $process->getOutput();
};

$processEventSuccess = new ProcessEvent(ProcessEvent::EVENT_SUCCESS, $successCallback);

$process1 = new AsyncProcess(new Process(['ls', '/tmp']));
$process1->addProcessEvent($processEventSuccess);

$process2 = new AsyncProcess(new Process(['ls', '/home']));
$process2->addProcessEvent($processEventSuccess);

$multiProcess = new MultiProcess();
$multiProcess
    ->addAsyncProcess($process1)
    ->addAsyncProcess($process2)
;

$multiProcess->run();
````

## Using Event

event list:
- process start
- process failed
- process finish successful

````
use Wizaplace\Process\AsyncProcess;
use Wizaplace\Process\MultiProcess;
use Wizaplace\Process\ProcessEvent;
use Symfony\Component\Process\Process;

$successCallback = function (Process $process, $startTime, $finishTime) {
    echo $process->getOutput();
};

$failedCallback = function (Process $process, $startTime, $finishTime) {
    echo $process->getErrorOutput();
};

$processEventSuccess = new ProcessEvent(ProcessEvent::EVENT_SUCCESS, $successCallback);
$processEventFailed = new ProcessEvent(ProcessEvent::EVENT_FAILED, $failedCallback);

$process1 = new AsyncProcess(new Process(['ls', '/t11mp']));
$process1
    ->addProcessEvent($processEventSuccess)
    ->addProcessEvent($processEventFailed)
;

$process2 = new AsyncProcess(new Process(['ls', '/home']));
$process2
    ->addProcessEvent($processEventSuccess)
    ->addProcessEvent($processEventFailed)
;

$multiProcess = new MultiProcess();
$multiProcess
    ->addAsyncProcess($process1)
    ->addAsyncProcess($process2)
;

$multiProcess->run();
````
