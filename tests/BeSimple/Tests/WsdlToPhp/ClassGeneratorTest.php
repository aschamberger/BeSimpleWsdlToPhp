<?php

/*
 * This file is part of BeSimpleWsdlToPhp.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 * (c) Andreas Schamberger <mail@andreass.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\Tests\WsdlToPhp;

use BeSimple\WsdlToPhp\ClassGenerator;

class ClassGeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $testData;

    protected function setUp()
    {
        $filename = dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixtures/ClassGeneratorTestData.php';
        $this->testData = require $filename;
    }

    public function testGenerator()
    {
        $generator = new ClassGenerator(
            array('access' => 'protected', 'generate_constructor' => true, 'instance_on_getter' => true),
            array('Brand' => array('properties' => array(array('name' => 'someVar'))))
        );
        $targetDir = sys_get_temp_dir();

        $target = $generator->writeClass($this->testData['customer'], $targetDir);
        $file1 = dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixtures/My/Webservices/Customer.php';
        $this->assertFileEquals($file1, $target);
//         unset($file1);
        $target = $generator->writeClass($this->testData['car'], $targetDir);
        $file2 = dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixtures/My/Webservices/Car.php';
        $this->assertFileEquals($file2, $target);
//         unset($file2);
    }

    public function testGeneratorExtends()
    {
        $generator = new ClassGenerator();
        $targetDir = sys_get_temp_dir();

        $target = $generator->writeClass($this->testData['online_customer'], $targetDir);
        $file = dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixtures/My/Webservices/OnlineCustomer.php';
        $this->assertFileEquals($file, $target);
//         unset($file);
    }

    public function testGeneratorEmpty()
    {
        $generator = new ClassGenerator();
        $targetDir = sys_get_temp_dir();

        $target = $generator->writeClass($this->testData['empty'], $targetDir);
        $file = dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixtures/My/Webservices/EmptyClass.php';
        $this->assertFileEquals($file, $target);
//         unset($file);
    }
}
