<?php
/**
 * @package     performance-test
 * @subpackage
 *
 * @copyright   Copyright (C) 2017 Web Imp Pte Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
namespace Codeception\Extension;

class PerformanceTest extends \Codeception\Extension
{
    // maximum time allowed for a step to perform (seconds)
    protected $config = [
        'benchmark' => 3,
        'padding'   => 120,
    ];

    private static $testTimes        = [];
    private static $slowStepsByTest  = [];
    private static $tmpCurrentTest   = 0;
    private static $tmpStepStartTime = 0;


    public function _initialize()
    {
        // turn on printing for this extension
        $this->options['silent'] = false;
    }

    // we are listening for events
    static $events = array(
        'test.before'  => 'beforeTest',
        'test.end'     => 'afterTest',
        'suite.before' => 'beforeSuite',
        'suite.after'  => 'afterSuite',
        'step.before'  => 'beforeStep',
        'step.after'   => 'afterStep'
    );

    // we are printing test status and time taken
    public function beforeTest(\Codeception\Event\TestEvent $e)
    {
        $this->tmpCurrentTest = \Codeception\Test\Descriptor::getTestAsString($e->getTest());
    }

    // we are printing test status and time taken
    public function beforeStep(\Codeception\Event\StepEvent $e)
    {
        $this->tmpStepStartTime = round((float) microtime(true), 2);
    }

    // we are printing test status and time taken
    public function afterStep(\Codeception\Event\StepEvent $e)
    {
        $benchmark   = round((float) $this->config['benchmark'], 2);
        $stepEndTime = round((float) microtime(true), 2);
        $stepTime    = $stepEndTime - $this->tmpStepStartTime;

        if ($stepTime > $benchmark) {
            $currentStep = (string) $e->getStep();
            $step        = new \stdClass;
            $step->name  = $currentStep;
            $step->time  = round($stepTime, 2);

            $this->slowStepsByTest[$this->tmpCurrentTest][] = $step;
        }
    }

    public function afterTest(\Codeception\Event\TestEvent $e)
    {
        $test       = new \stdClass;
        $test->name = \Codeception\Test\Descriptor::getTestAsString($e->getTest());

        // stack overflow: http://stackoverflow.com/questions/16825240/how-to-convert-microtime-to-hhmmssuu
        $test->time = round((float) $e->getTime(), 2);

        $this->testTimes[] = $test;
    }

    // reset times and slow test arrays, in case multiple suites are launched
    public function beforeSuite(\Codeception\Event\SuiteEvent $e)
    {
        $this->testTimes       = [];
        $this->slowStepsByTest = [];
    }

    public function afterSuite(\Codeception\Event\SuiteEvent $e)
    {
        $this->writeln('');
        $this->writeln(str_pad('<bold>Slow Steps (more than ' . $this->config['benchmark'] . 's)</bold> ', $this->config['padding'], '-'));

        foreach ($this->slowStepsByTest as $testname => $steps) {
            $test_role = substr($testname, 0, stripos($testname, ':') + 1);
            $test_name = substr($testname, stripos($testname, ':') + 1);

            $testname = '<focus>' . $test_role . '</focus>' . $test_name;

            $this->writeln($testname);

            foreach ($steps as $step) {
                $this->writeln('  ' . $step->name . ' <info>(' . $step->time . 's)</info>');
            }
        }

        $this->writeln(str_pad('', $this->config['padding'], '-'));
    }
}
