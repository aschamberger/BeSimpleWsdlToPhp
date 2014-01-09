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

include 'vendor/autoload.php';

$opts = array(
    'wsdl' => array(
        'shortKey' => 'w',
        'access'   => 'required',
        'default'  => null,
        'doc'      => 'Required! Path or url to wsdl file.',
    ),
    'client' => array(
        'shortKey' => 'c',
        'access'   => 'optional',
        'default'  => false,
        'doc'      => 'Name of client class, if it is empty client will not be generated.',
    ),
    'parent' => array(
        'shortKey' => null,
        'access'   => 'optional',
        'default'  => false,
        'doc'      => 'Parent of Client class. Default value: \\SoapClient',
    ),
    'namespace' => array(
        'shortKey' => 'n',
        'access'   => 'optional',
        'default'  => false,
        'doc'      => 'Root namespace of generated classes.',
    ),
    'soap_version' => array(
        'shortKey' => 'v',
        'access'   => 'optional',
        'default'  => SOAP_1_1,
        'doc'      => 'Soap version: 1 => 1.1 or 2 => 1.2. Default value: 1 => 1.1',
    ),
    'output_dir' => array(
        'shortKey' => 'o',
        'access'   => 'optional',
        'default'  => getcwd(),
        'doc'      => 'Output dir for classes. Default current dir.',
    ),
    'extension' => array(
        'shortKey' => null,
        'access'   => 'optional',
        'default'  => 'php',
        'doc'      => 'Extension of generated files. Default value: php',
    ),
    'indent' => array(
        'shortKey' => null,
        'access'   => 'optional',
        'default'  => 4,
        'doc'      => 'How much indent would be used in generated files. Default value: 4',
    ),
    'empty_parameter_name' => array(
        'shortKey' => null,
        'access'   => 'optional',
        'default'  => '_',
        'doc'      => 'Default name of parameter without provided name. Default value: _',
    ),
    'overwrite' => array(
        'shortKey' => null,
        'access'   => 'no_values',
        'default'  => true,
        'doc'      => 'Disable overwrite present files. It does not have parameters.',
    ),
    'backup' => array(
        'shortKey' => null,
        'access'   => 'no_values',
        'default'  => true,
        'doc'      => 'Disable backup old files. It does not have parameters.',
    ),
    'generate_constructor' => array(
        'shortKey' => null,
        'access'   => 'no_values',
        'default'  => false,
        'reverse'  => true,
        'doc'      => 'Generate constructor in Types. It does not have parameters.',
    ),
    'instance_on_getter' => array(
        'shortKey' => null,
        'access'   => 'no_values',
        'default'  => false,
        'reverse'  => true,
        'doc'      => 'Make instance of related class on getter when property is null. ' . "\n\t" .
            'It does not have parameters. It does not work with access=public',
    ),
    'access' => array(
        'shortKey' => null,
        'access'   => 'optional',
        'default'  => 'public',
        'doc'      => 'Access level to properties. Default value: public.',
    ),
    'wsdl2java_style' => array(
        'shortKey' => null,
        'access'   => 'no_values',
        'default'  => true,
        'doc'      => 'Disable generation of wsdl2java style namespaces. It does not have parameters.',
    ),
);

$shortOptions = '';
$longOptions = array();
foreach ($opts as $key => $vals) {
    $mark = '';
    switch ($vals['access']) {
        case 'no_values';
            break;
        case 'optional';
            $mark = '::';
            break;
        case 'required';
            $mark = ':';
            break;
        default:
//            throw new \Exception();
    }
    if ($vals['shortKey']) {
        $shortOptions .= $vals['shortKey'] . $mark;
    }
    $longOptions[] = $key . $mark;
}
$options = getopt($shortOptions, $longOptions);

$outputArr = array();
$defaultOptions = array();
foreach ($opts as $key => $vals) {
    $defaultOptions[$key] = $vals['default'];
    $optParam = '<option>';
    $optParamLong = '=';
    if ('no_values' === $vals['access']) {
        $optParam = '';
        $optParamLong = '';
    }

    $shortKey = '';
    if ($vals['shortKey']) {
        $shortKey = " or -{$vals['shortKey']}{$optParam}";
    }

    $outputArr[] = "--{$key}$optParamLong{$optParam}{$shortKey}" . (empty($vals['doc'])?'':' — ' . $vals['doc']);

    if (isset($options[$vals['shortKey']])) {
        $options[$key] = $options[$vals['shortKey']];
        unset($options[$vals['shortKey']]);
    }

    if (!empty($vals['reverse']) && isset($options[$key])) {
        $options[$key] = !$options[$key];
    }
}
$options = array_merge($defaultOptions, $options);

if (empty($options['wsdl'])) {
    die(
        'Path to WSDL file is required!' . PHP_EOL .
        'All parameters:' . PHP_EOL .
        implode(PHP_EOL, $outputArr) . PHP_EOL
    );
}

echo "Starts\n";
$parser = new WsdlParser($options['wsdl'], $options['soap_version'], $options);
$wsdlTypes = $parser->getWsdlTypes();
if ($parser->hasErrors()) {
    echo "Errors:\n";
    foreach ($parser->getErrors() as $error) {
        echo $error->toString(), "\n";
    }
}

echo "Generates\n";
$generator = new ClassGenerator($options, $wsdlTypes);
$classmapTypes = array();
if (!empty($wsdlTypes)) {
    foreach ($wsdlTypes as $type) {
        if (!empty($options['namespace'])) {
            $type['namespace'] = $options['namespace'] . (empty($type['namespace']) ? '' : '\\' . $type['namespace']);
        }
        $file = $generator->writeClass($type, $options['output_dir']);
        echo 'written file ' . $file . PHP_EOL;
        $classmapTypes[$type['name']] = $type['namespace'] .'\\' . $type['name'];
    }
} else {
    echo "No types found\n";
}

if (false !== $options['client']) {
    $generator = new ClientGenerator($options, $wsdlTypes);
    $data = array(
        'wsdl'       => $options['wsdl'],
        'namespace'  => $options['namespace'],
        'parent'     => $options['parent'],
        'name'       => $options['client'],
        'operations' => $parser->getWsdlOperations(),
        'types'      => $classmapTypes,
    );
    $file = $generator->writeClass($data, $options['output_dir']);
    echo 'written file ' . $file . PHP_EOL;
}
