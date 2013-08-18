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
            $lines[] = $this->generateProperty($property);
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
                $lines[] = $this->spaces . ' * - self::' . strtoupper($property['name']) . '_' . strtoupper($enum) . ' (' . $enum . ')';
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
        $lines[] = $this->spaces . 'public $' . $property['name'] . ($property['isNull']?' = null':'') . ';';

        return implode("\n", $lines);
    }
}
