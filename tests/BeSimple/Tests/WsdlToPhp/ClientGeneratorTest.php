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

use BeSimple\WsdlToPhp\ClientGenerator;

class ClientGeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $testData;

    protected function setUp()
    {
        $filename = dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixtures/ClientGeneratorTestData.php';
        $this->testData = require $filename;
    }

    public function testGenerator()
    {
        $generator = new ClientGenerator();
        $targetDir = sys_get_temp_dir();
        $targetDir = dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixtures';

        $target = $generator->writeClass($this->testData, $targetDir);
        $file = dirname(__DIR__).DIRECTORY_SEPARATOR.'Fixtures/My/Webservices/Client.php';
        $this->assertFileEquals($file, $target);
//         unset($file);
    }
}