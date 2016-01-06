<?php

class TestListener extends PHPUnit_Framework_BaseTestListener
{

    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        printf("Error Code while running test '%s': '%s'.\n", $test->getName(), $e->getCode());
    }

}