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

namespace BeSimple\WsdlToPhp;

/**
 * Client generator for WSDL service.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class ClientGenerator extends AbstractClassGenerator
{

    /**
     * Generate class.
     *
     * @param array(string=>mixed) $data Data array
     *
     * @return string
     */
    public function generateClass($data)
    {
        $lines = array();
        $lines[] = '<?php';
        $lines[] = '';
        $lines[] = $this->generateNamespace($data);

        if (empty($data['parent'])) {
            $data['parent'] = '\\SoapClient';
        }

        $lines[] = 'use ' . $data['parent'] . ' as BaseSoapClient;';
        $lines[] = '';
        $lines[] = $this->generateDocBlock($data);
        $lines[] = $this->generateClassName($data);
        $lines[] = '{';
        $lines[] = $this->generateClassBody($data);
        $lines[] = '}';

        return implode("\n", $lines);
    }

    /**
     * Generate class name.
     *
     * @param array(string=>mixed) $data Data array
     *
     * @return string
     */
    protected function generateClassName($data)
    {
        $class = 'class ' . $this->createValidClassName($data['name'], $data['namespace']);
        $class .= ' extends BaseSoapClient';

        return $class;
    }

    /**
     * Generate class body.
     *
     * @param array(string=>mixed) $data Data array
     *
     * @return string
     */
    protected function generateClassBody($data)
    {
        $lines = array();

        $lines[] = $this->generateConstructor($data);

        foreach ($data['operations'] as $operation) {
            $lines[] = $this->generateOperation($operation);
        }

        return implode("\n\n", $lines);
    }

    /**
     * Generate constructor.
     *
     * @param array(string=>mixed) $data Data array
     *
     * @return string
     */
    protected function generateConstructor($data)
    {
        $lines = array();
        $lines[] = $this->spaces . 'protected $classMap = array(';
        if (!empty($data['types'])) foreach ($data['types'] as $name => $type) {
            $lines[] = str_repeat($this->spaces, 2) . "'" . $name . "' => '" . $type . "',";
        }
        $lines[] = $this->spaces . ');';
        $lines[] = '';
        $lines[] = $this->spaces . '/**';
        $lines[] = $this->spaces . ' * Constructor.';
        $lines[] = $this->spaces . ' *';
        $lines[] = $this->spaces . ' * @param string               $wsdl    WSDL file';
        $lines[] = $this->spaces . ' * @param array(string=>mixed) $options Options array';
        $lines[] = $this->spaces . ' */';
        $lines[] = $this->spaces . 'public function __construct($wsdl, array $options = array())';
        $lines[] = $this->spaces . '{';
        $lines[] = $this->spaces . $this->spaces . 'if (!isset($options[\'classmap\'])) {';
        $lines[] = $this->spaces . $this->spaces . $this->spaces . '$options[\'classmap\'] = $this->getClassMap();';
        $lines[] = $this->spaces . $this->spaces . '}';
        $lines[] = '';
        $lines[] = $this->spaces . $this->spaces . 'return parent::__construct($wsdl, $options);';
        $lines[] = $this->spaces . '}';
        $lines[] = '';
        $lines[] = $this->spaces . 'public function getClassMap()';
        $lines[] = $this->spaces . '{';
        $lines[] = $this->spaces . $this->spaces . 'return $this->classMap;';
        $lines[] = $this->spaces . '}';

        return implode("\n", $lines);
    }


    /**
     * Generate operation.
     *
     * @param array(string=>string) $operation Operationinformation
     *
     * @return string
     */
    protected function generateOperation($operation)
    {
        $lines = array();
        $lines[] = $this->spaces . '/**';
        $lines[] = $this->spaces . ' * ' . $operation['name'];
        if (!empty($operation['documentation'])) {
            $lines[] = $this->spaces . ' *';
            $width = 80-strlen($this->spaces)-3;
            $break = "\n" . $this->spaces . " * ";
            $lines[] = $this->spaces . ' * ' . wordwrap($operation['documentation'], $width, $break);
        }
        $lines[] = $this->spaces . ' *';
        foreach ($operation['parameters'] as $name => $type) {
            $lines[] = $this->spaces . ' * @param ' . $type . ' $' . $name;
        }
        if (count($operation['parameters']) > 0) {
            $lines[] = $this->spaces . ' *';
        }
        $lines[] = $this->spaces . ' * @return ' . $operation['return'];
        $lines[] = $this->spaces . ' */';
        $lines[] = $this->spaces . "public function " . $operation['name'] . '(' .
            $this->generateFunctionArguments($operation['parameters']) . ')';
        $lines[] = $this->spaces . '{';
        if (isset($operation['wrapParameters'])) {

            if ($this->getOption('generate_constructor')) {
                throw new Exception('It does not implemented yet');
            } else {
                $lines[] = $this->spaces . $this->spaces . '$parameters = new ' . $operation['wrapParameters'] . '();';
                if ('public' == $this->getOption('access')) {
                    foreach ($operation['parameters'] as $name => $type) {
                        $lines[] = $this->spaces . $this->spaces . '$parameters->' . $name . ' = $' . $name . ';';
                    }
                } else {
                    foreach ($operation['parameters'] as $name => $type) {
                        $lines[] = $this->spaces . $this->spaces . '$parameters->set' . ucfirst($name) . '($' . $name . ');';
                    }
                }
            }
        } else {
            $lines[] = $this->spaces . $this->spaces . '$parameters = func_get_args();';
        }
        $lines[] = '';
        $lines[] = $this->spaces . $this->spaces . 'return $this->__soapCall(\'' .
            $operation['name'] . '\', array(\'parameters\' => $parameters));';
        $lines[] = $this->spaces . '}';

        return implode("\n", $lines);
    }
}
