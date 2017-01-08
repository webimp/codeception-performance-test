<?php
/**
 * @package     performance-test
 * @subpackage
 *
 * @copyright   Copyright (C) 2017 Web Imp Pte Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace Codeception\Extension;

use Codeception\Events;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Event\StepEvent;

class PerformanceTest extends \Codeception\Platform\Extension
{
    /**
     * Maximum time in second allowed for a step to be performant
     *
     * @var int
     */
    public static $maxStepPerformanceTime = 1;
    public static $testTimes              = [];
    public static $slowStepsByTest        = [];
    public static $tmpCurrentTest         = 0;
    public static $tmpStepStartTime       = 0;
    private static $padding               = 50;


    public function _initialize()
    {
        $this->options['silent'] = false; // turn on printing for this extension
        //$this->_reconfigure(['settings' => ['silent' => true]]); // turn off printing for everything else
    }

    // we are listening for events
    static $events = array(
        Events::TEST_BEFORE  => 'beforeTest',
        Events::TEST_END     => 'afterTest',
        Events::SUITE_AFTER  => 'afterSuite',
        Events::STEP_BEFORE  => 'beforeStep',
        Events::STEP_AFTER   => 'afterStep'
    );

    // we are printing test status and time taken
    public function beforeTest(TestEvent $e)
    {
        self::$tmpCurrentTest = \Codeception\Test\Descriptor::getTestAsString($e->getTest());
    }

    // we are printing test status and time taken
    public function beforeStep(StepEvent $e)
    {
        list($usec, $sec)       = explode(" ", microtime());
        self::$tmpStepStartTime = (float) $sec;
    }

    // we are printing test status and time taken
    public function afterStep(StepEvent $e)
    {
        list($usec, $sec) = explode(" ", microtime());
        $stepEndTime      = (float) $sec;

        $stepTime = $stepEndTime - self::$tmpStepStartTime;

        // If the Step has taken more than $maxStepPerformanceTime seconds
        if ($stepTime > self::$maxStepPerformanceTime) {
            $currentStep = (string) $e->getStep();
            $step        = new \stdClass;
            $step->name  = $currentStep;
            $step->time  = $stepTime;

            self::$slowStepsByTest[self::$tmpCurrentTest][] = $step;
        }
    }

    public function afterTest(TestEvent $e)
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

    public function afterSuite(SuiteEvent $e)
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
