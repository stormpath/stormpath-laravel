<?php

class TestListener extends PHPUnit_Framework_BaseTestListener
{

    private $errors;

    public function __construct()
    {
        $this->printer = new PHPUnit_Util_Printer();
    }


    public function addError(PHPUnit_Framework_Test $test, Exception $e, $time)
    {
        $this->errors[] = sprintf("Error Code while running test '%s': '%s'.\n", $test->getName(), $e->getCode());
    }

    public function __destruct() {

        if(!empty($this->errors)) {
            $this->printer->write("Debug Information for Errors:\n");

            foreach($this->errors as $error) {
                $this->printer->write("{$error}");
            }
        }

    }


}