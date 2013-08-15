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

include __DIR__.'/../vendor/autoload.php';

$opts = array(
    'w' => 'wsdl:',
    'c' => 'client:',
    's' => 'server:',
    'n' => 'namespace:',
    'v' => 'soap_version:',
    'o' => 'output_dir:',
);

$options = getopt(implode(':', array_keys($opts)) . ':', array_values($opts));

if (isset($options['w'])) {
    $wsdlFile = $options['w'];
} elseif (isset($options['wsdl'])) {
    $wsdlFile = $options['wsdl'];
} else {
    $output = 'Optional params:' . PHP_EOL;

    foreach ($opts as $key => $val) {
        $val = substr($val, 0, strlen($val) - 1);
        $output .= "-{$key} or --{$val}" . PHP_EOL;
    }

    die(
        'Parameter -w <file> or --wsdl <file> required!' . PHP_EOL . PHP_EOL . $output
    );
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

echo "Starts\n";
$p = new WsdlParser($wsdlFile, $soapVersion);
$wsdlTypes = $p->getWsdlTypes();
echo "Generates\n";
$generator = new ClassGenerator();
$classmapTypes = array();
if (!empty($wsdlTypes)) {
    foreach ($wsdlTypes as $type) {
        if (!empty($namespace)) {
            $type['namespace'] = $namespace . (empty($type['namespace']) ? '' : '\\' . $type['namespace']);
        }
        $file = $generator->writeClass($type, $outputDir);
        echo 'written file ' . $file . PHP_EOL;
        $classmapTypes[$type['name']] = '\\' . $type['namespace'] .'\\' . $type['name'];
    }
} else {
    echo "No types found\n";
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
