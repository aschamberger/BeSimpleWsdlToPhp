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

use BeSimple\WsdlToPhp\WsdlParser;
use BeSimple\WsdlToPhp\ClassGenerator;
use BeSimple\WsdlToPhp\ClientGenerator;

error_reporting(E_ALL | E_STRICT);

spl_autoload_register(function($class) {
    if (0 === strpos($class, 'BeSimple\WsdlToPhp\\')) {
        $path = __DIR__.'/../src/'.strtr($class, '\\', '/').'.php';
        if (file_exists($path) && is_readable($path)) {
            require_once $path;

            return true;
        }
    }
});

$shortopts = 'w:c:s:n:v:o:';
$longopts = array(
    'wsdl:',
    'client:',
    'server:',
    'namespace:',
    'soap_version:',
    'output_dir',
);

$options = getopt($shortopts, $longopts);

if (isset($options['w'])) {
    $wsdlFile = $options['w'];
} elseif (isset($options['wsdl'])) {
    $wsdlFile = $options['wsdl'];
} else {
    exit('Parameter -w <file> or --wsdl <file> required!' . PHP_EOL);
}

if (isset($options['c'])) {
    $client = $options['c'];
} elseif (isset($options['client'])) {
    $client = $options['client'];
} else {
    $client = false;
}

if (isset($options['s'])) {
    $server = $options['s'];
} elseif (isset($options['server'])) {
    $server = $options['server'];
} else {
    $server = false;
}

if (isset($options['n'])) {
    $namespace = $options['n'];
} elseif (isset($options['namespace'])) {
    $namespace = $options['namespace'];
} else {
    $namespace = false;
}

if (isset($options['v']) && $options['v'] == '1.2') {
    $soapVersion = SOAP_1_2;
} elseif (isset($options['soap_version']) && $options['soap_version'] == '1.2') {
    $soapVersion = SOAP_1_2;
} else {
    $soapVersion = SOAP_1_1;
}

if (isset($options['o'])) {
    $outputDir = $options['o'];
} elseif (isset($options['output_dir'])) {
    $outputDir = $options['output_dir'];
} else {
    $outputDir = getcwd();
}

$p = new WsdlParser($wsdlFile, $soapVersion);
$wsdlTypes = $p->getWsdlTypes();

$generator = new ClassGenerator();
$classmapTypes = array();
foreach ($wsdlTypes as $type) {
    $file = $generator->writeClass($type, $outputDir);
    echo 'written file ' . $file . PHP_EOL;
    $classmapTypes[$type['name']] = '\\' . $type['namespace'] .'\\' . $type['name'];
}

if (false !== $client) {
    $generator = new ClientGenerator();
    $data = array(
        'wsdl' => $wsdlFile,
        'namespace' => $namespace,
        'name' => $client,
        'operations' => $p->getOperations(),
        'types' => $classmapTypes,
    );
    $file = $generator->writeClass($data, $outputDir);
    echo 'written file ' . $file . PHP_EOL;
}