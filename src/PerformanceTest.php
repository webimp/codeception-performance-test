<?php
/**
 * @package     performance-test
 * @subpackage
 *
 * @copyright   Copyright (C) 2017 Web Imp Pte Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

class PerformanceTest extends \Codeception\Extension
{
    // maximum time allowed for a step to perform (seconds)
    protected $config = ['benchmark' => 3];

    private static $testTimes             = [];
    private static $slowStepsByTest       = [];
    private static $tmpCurrentTest        = 0;
    private static $tmpStepStartTime      = 0;
    private static $padding               = 50;


    public function _initialize()
    {
        $this->options['silent'] = false; // turn on printing for this extension
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
        self::$tmpCurrentTest = \Codeception\Test\Descriptor::getTestAsString($e->getTest());
    }

    // we are printing test status and time taken
    public function beforeStep(\Codeception\Event\StepEvent $e)
    {
        list($usec, $sec)       = explode(" ", microtime());
        self::$tmpStepStartTime = (float) $sec;
    }

    // we are printing test status and time taken
    public function afterStep(\Codeception\Event\StepEvent $e)
    {
        list($usec, $sec) = explode(" ", microtime());
        $stepEndTime      = (float) $sec;

        $stepTime = $stepEndTime - self::$tmpStepStartTime;

        if ($stepTime > $this->config['benchmark']) {
            $currentStep = (string) $e->getStep();
            $step        = new \stdClass;
            $step->name  = $currentStep;
            $step->time  = $stepTime;

            self::$slowStepsByTest[self::$tmpCurrentTest][] = $step;
        }
    }

    public function afterTest(\Codeception\Event\TestEvent $e)
    {
        $test       = new \stdClass;
        $test->name = \Codeception\Test\Descriptor::getTestAsString($e->getTest());

        // stack overflow: http://stackoverflow.com/questions/16825240/how-to-convert-microtime-to-hhmmssuu
        $seconds_input = $e->getTime();
        $seconds       = (int)($milliseconds = (int)($seconds_input * 1000)) / 1000;
        $time          = ($seconds % 60);

        $test->time = $time;

        self::$testTimes[] = $test;
    }

    // reset times and slow test arrays, in case multiple suites are launched
	public function beforeSuite(\Codeception\Event\SuiteEvent $e)
	{
		self::$testTimes       = [];
		self::$slowStepsByTest = [];
    }

    public function afterSuite(\Codeception\Event\SuiteEvent $e)
    {
        $this->writeln(str_pad('Slow Steps (more than ' . self::$maxStepPerformanceTime . 's) ', $self::padding, '-'));

        foreach (self::$slowStepsByTest as $testname => $steps) {
            $this->writeln(str_pad(' ' . $testname . ' ', $self::padding, '-'));

            foreach ($steps as $step) {
                $this->writeln('  ' . $step->name . '(' . $step->time . 's)');
            }
        }
    }
}
