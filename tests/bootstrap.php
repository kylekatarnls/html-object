<?php

require __DIR__ . '/../vendor/autoload.php';

if (!class_exists('PHPUnit_Framework_DOMTestCase')) {
    class_alias('PHPUnit\\Framework\\DOMTestCase', 'PHPUnit_Framework_DOMTestCase');
}
