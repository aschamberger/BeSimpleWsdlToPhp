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
 * Class generator for WSDL types.
 *
 * $generator = new ClassGenerator();
 * $generator->writeClass($data, $dir);
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class ClassGenerator extends AbstractClassGenerator
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
        if ($namespace = $this->generateNamespace($data)) {
            $lines[] = $namespace;
        }
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
        if (!empty($data['parent'])) {
            $class .= ' extends ' . $this->createValidClassName($data['parent'], $data['namespace']);
        }

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

        foreach ($data['properties'] as $property) {
            $enum = $this->generateEnumConstants($property);
            if (!empty($enum)) {
                $lines[] = $enum;
            }
        }

        foreach ($data['properties'] as $property) {
            if ($line = $this->generateProperty($property)) {
                $lines[] = $line;
            }
        }

        if ($constructor = $this->generateConstructor($data)) {
            $lines[] = $constructor;
        }

        foreach ($data['properties'] as $property) {
            if ($line = $this->generateGettersAndSetters($data, $property)) {
                $lines[] = $line;
            }
        }

        return implode("\n\n", $lines);
    }

    /**
     * Generate enum constants.
     *
     * @param array(string=>string) $property Property information
     *
     * @return string
     */
    protected function generateEnumConstants($property)
    {
        $name = empty($property['name'])?'':strtoupper($property['name']) . '_';

        $lines = array();
        if (isset($property['enum'])) {
            foreach ($property['enum'] as $enum) {
                $lines[] = $this->spaces . 'const ' . $name . strtoupper($enum) . ' = \'' . $enum . '\';';
            }
        }

        return implode("\n", $lines);
    }

    protected function getProperties($properties, $isRequired)
    {
        foreach ($properties as $key => $val) {
            if ($isRequired == $val['isNull']) {
                unset($properties[$key]);
            }
        }
        return $properties;
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
        if (empty($data['properties']) ||
            empty($data['properties'][0]['name']) ||
            !$this->getOption('generate_constructor')
        ) {
            return '';
        }
        $lines = array();
        $lines[] = $this->spaces . '/**';
        $lines[] = $this->spaces . ' * Constructor.';
        $lines[] = $this->spaces . ' *';
        $reqArgs = $this->getProperties($data['properties'], true, $data);
        $optArgs = $this->getProperties($data['properties'], false, $data);
        $ops = array();
        foreach ($reqArgs + $optArgs as $property) {
            if (empty($property['name'])) continue;
            $localName = lcfirst($property['name']);
            $lines[] = $this->spaces . " * @param {$property['phpType']} \${$localName}";
            $ops[] = $this->generateFunctionArguments(
                array($localName => $property['phpType']),
                !$property['isNull']
            );

        }
        $lines[] = $this->spaces . ' */';
        $lines[] = $this->spaces . 'public function __construct(' . implode(', ', $ops) . ')';
        $lines[] = $this->spaces . '{';
        foreach ($reqArgs + $optArgs as $property) {
            $lines[] = $this->spaces . $this->spaces . "\$this->{$property['name']} = \$" .
                lcfirst($property['name']) . ';';
        }
        $lines[] = $this->spaces . '}';

        return implode("\n", $lines);
    }

    /**
     * Generate property.
     *
     * @param array(string=>string) $property Property information
     *
     * @return string
     */
    protected function generateProperty($property)
    {
        if (empty($property['name'])) return '';

        $lines = array();
        $lines[] = $this->spaces . '/**';
        $lines[] = $this->spaces . ' * ' . $property['name'];
        if (!empty($property['documentation'])) {
            $lines[] = $this->spaces . ' *';
            $width = 80-strlen($this->spaces)-3;
            $break = "\n" . $this->spaces . " * ";
            $lines[] = $this->spaces . ' * ' . wordwrap($property['documentation'], $width, $break);
        }
        if (isset($property['enum'])) {
            $lines[] = $this->spaces . ' *';
            $lines[] = $this->spaces . ' * The property can have one of the following values:';
            foreach ($property['enum'] as $enum) {
                $lines[] = $this->spaces . ' * - self::' . strtoupper($property['name']) . '_' . strtoupper($enum) .
                    ' (' . $enum . ')';
            }
        }
        $lines[] = $this->spaces . ' *';
        $lines[] = $this->spaces . ' * The property has the following characteristics/restrictions:';
        $lines[] = $this->spaces . ' * - SchemaType: ' . $property['wsdlType'];
        foreach ($property['restrictions'] as $restriction => $value) {
            $lines[] = $this->spaces . " * - {$restriction}: {$value}";
        }
        $lines[] = $this->spaces . ' *';
        $lines[] = $this->spaces . ' * @var ' . $property['phpType'];
        $lines[] = $this->spaces . ' */';
        $lines[] = $this->spaces . $this->getOption('access') . ' $' . $property['name'] .
            ($property['isNull']?' = null':'') . ';';

        return implode("\n", $lines);
    }

    /**
     * Generate getters and setters
     *
     * @param array(string=>mixed) $data Data array
     * @param array(string=>string) $property Property information
     *
     * @return string
     */
    protected function generateGettersAndSetters($data, $property)
    {
        if (empty($property['name']) || 'public' == $this->getOption('access')) {
            return '';
        }

        $localName = lcfirst($property['name']);

        $lines = array();
        $lines[] = $this->spaces . '/**';
        $lines[] = $this->spaces . ' * @param ' . $property['phpType'] . ' $' . $localName;
        $lines[] = $this->spaces . ' *';
        $lines[] = $this->spaces . ' * @return ' . $this->createValidClassName($data['name'], $data['namespace']);
        $lines[] = $this->spaces . ' */';
        $lines[] = $this->spaces . 'public function set' . ucfirst($property['name']) . '(' .
            $this->generateFunctionArguments(array($localName => $property['phpType']), true, $data)  . ')';
        $lines[] = $this->spaces . '{';
        $lines[] = $this->spaces . $this->spaces . '$this->' . $property['name'] . ' = $' . $localName . ';';
        $lines[] = $this->spaces . $this->spaces . 'return $this;';
        $lines[] = $this->spaces . '}';
        $lines[] = '';
        $lines[] = $this->spaces . '/**';
        $lines[] = $this->spaces . ' * @return ' . $property['phpType'];
        $lines[] = $this->spaces . ' */';
        $lines[] = $this->spaces . 'public function get' . ucfirst($property['name']) . '()';
        $lines[] = $this->spaces . '{';
        if (!in_array($property['phpType'], self::$phpTypes) &&
            !empty($this->wsdlTypes[$property['phpType']]['properties'][0]['name']) &&
            $this->getOption('instance_on_getter')
        ) {
            $lines[] = $this->spaces . $this->spaces . 'if (null === $this->' . $property['name'] . ') {';
            $lines[] = $this->spaces . $this->spaces . $this->spaces . '$this->' . $property['name'] . ' = new ' .
                $property['phpType'] . '();';
            $lines[] = $this->spaces . $this->spaces . '}';
        }
        $lines[] = $this->spaces . $this->spaces . 'return $this->' . $property['name'] . ';';
        $lines[] = $this->spaces . '}';

        return implode("\n", $lines);
    }
}
