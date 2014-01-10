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
            array('access' => 'protected', 'generate_constructor' => true)
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

    public function testGeneratorEnum()
    {
        $generator = new ClassGenerator(
            array('access' => 'protected', 'generate_constructor' => true)
        );
        $targetDir = sys_get_temp_dir();

        $target = $generator->writeClass(
            array(
                'wsdl' => 'http://localhost/',
                'namespace' => '',
                'name' => 'Enum',
                'properties' => array(
                    array(
                        'name' => '',
                        'restrictions' => array(),
                        'enum' => array(
                            'Unassigned',
                            'Personal',
                            'Business'
                        ),
                        'wsdlType' => 'xs:string',
                        'phpType' => 'string',
                        'isNull' => false
                    )
                )
            ),
            $targetDir
        );
        $file1 = $this->fixturesDir.'/My/Webservices/Enum.php';
        $this->assertFileEquals($file1, $target);
    }

    public function testGeneratorTypeHinting()
    {
        $generator = new ClassGenerator(
            array('access' => 'protected', 'generate_constructor' => true, 'instance_on_getter' => true),
            array(
                'Brand' => array('properties' => array(array('name' => 'someVar'))),
                'Enum' => array('properties' => array(array('name' => '', 'enum' => array('enum_value')))),
            )
        );
        $targetDir = sys_get_temp_dir();

        $target = $generator->writeClass(
            array(
                'wsdl' => 'http://localhost/',
                'namespace' => '',
                'name' => 'TypeHinting',
                'properties' => array(
                    array(
                        'name' => 'brand',
                        'restrictions' => array(),
                        'enum' => array(),
                        'wsdlType' => 'xs:brand',
                        'phpType' => 'Brand',
                        'isNull' => false
                    ),
                    array(
                        'name' => 'enum',
                        'restrictions' => array(),
                        'enum' => array(),
                        'wsdlType' => 'xs:enum',
                        'phpType' => 'Enum',
                        'isNull' => false
                    ),
                    array(
                        'name' => 'arr',
                        'restrictions' => array(),
                        'enum' => array(),
                        'wsdlType' => 'xs:array',
                        'phpType' => 'array',
                        'isNull' => true
                    )
                )
            ),
            $targetDir
        );
        $file1 = $this->fixturesDir.'/My/Webservices/TypeHinting.php';
        $this->assertFileEquals($file1, $target);
    }
}
