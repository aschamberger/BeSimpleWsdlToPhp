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

namespace BeSimple\WsdlToPhp\Tests;

use BeSimple\WsdlToPhp\ClassGenerator;

class ClassGeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $fixturesDir;
    protected $testData;

    protected function setUp()
    {
        $this->fixturesDir = __DIR__ . "/Fixtures";
        $filename = $this->fixturesDir.'/ClassGeneratorTestData.php';
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
        $file1 = $this->fixturesDir.'/My/Webservices/Customer.php';
        $this->assertFileEquals($file1, $target);
        $target = $generator->writeClass($this->testData['car'], $targetDir);
        $file2 = $this->fixturesDir.'/My/Webservices/Car.php';
        $this->assertFileEquals($file2, $target);
    }

    public function testGeneratorExtends()
    {
        $generator = new ClassGenerator();
        $targetDir = sys_get_temp_dir();

        $target = $generator->writeClass($this->testData['online_customer'], $targetDir);
        $file = $this->fixturesDir.'/My/Webservices/OnlineCustomer.php';
        $this->assertFileEquals($file, $target);
    }

    public function testGeneratorEmpty()
    {
        $generator = new ClassGenerator();
        $targetDir = sys_get_temp_dir();

        $target = $generator->writeClass($this->testData['empty'], $targetDir);
        $file = $this->fixturesDir.'/My/Webservices/EmptyClass.php';
        $this->assertFileEquals($file, $target);
    }
}
