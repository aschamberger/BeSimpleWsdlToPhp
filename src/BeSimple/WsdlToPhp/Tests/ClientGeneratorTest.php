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

use BeSimple\WsdlToPhp\ClientGenerator;

class ClientGeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $fixturesDir;
    protected $testData;

    protected function setUp()
    {
        $this->fixturesDir = __DIR__ . "/Fixtures";
        $filename = $this->fixturesDir.'/ClientGeneratorTestData.php';
        $this->testData = require $filename;
    }

    public function testGenerator()
    {
        $generator = new ClientGenerator();
        $targetDir = sys_get_temp_dir();

        $target = $generator->writeClass($this->testData, $targetDir);
        $file = $this->fixturesDir.'/My/Webservices/Client.php';
        $this->assertFileEquals($file, $target);
    }
}
