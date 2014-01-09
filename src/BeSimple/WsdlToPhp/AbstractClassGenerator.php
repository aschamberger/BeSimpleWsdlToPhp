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
abstract class AbstractClassGenerator
{
    protected static $reservedKeywords = array(
        'abstract',
        'and',
        'array',
        'as',
        'break',
        'case',
        'catch',
        'class',
        'clone',
        'const',
        'continue',
        'declare',
        'default',
        'do',
        'else',
        'elseif',
        'enddeclare',
        'endfor',
        'endforeach',
        'endif',
        'endswitch',
        'endwhile',
        'extends',
        'final',
        'for',
        'foreach',
        'function',
        'global',
        'goto',
        'if',
        'implements',
        'interface',
        'instanceof',
        'namespace',
        'new',
        'or',
        'protected',
        'protected',
        'public',
        'static',
        'switch',
        'throw',
        'try',
        'use',
        'var',
        'while',
        'xor',
        '__CLASS__',
        '__DIR__',
        '__FILE__',
        '__LINE__',
        '__FUNCTION__',
        '__METHOD__',
        '__NAMESPACE__',
        'die',
        'echo',
        'empty',
        'exit',
        'eval',
        'include',
        'include_once',
        'isset',
        'list',
        'require',
        'require_once',
        'return',
        'print',
        'unset',
    );

    protected static $phpTypes = array(
        'boolean',
        'bool',
        'integer',
        'int',
        'float',
        'double',
        'string',
        'array',
        'object',
        'resource',
    );

    protected $backup = true;
    protected $extension = 'php';
    protected $overwrite = true;
    protected $spaces = 4;

    private $options = array(
        'overwrite' => true,
        'backup' => true,
        'extension' => 'php',
        'indent' => 4,
        'generate_constructor' => false,
        'instance_on_getter' => false,
        'access' => 'public',
    );

    /**
     * @var array
     */
    protected $wsdlTypes = array();

    /**
     * Constructor.
     *
     * Options:
     * - extension: PHP file extension
     * - indent:    Number of spaces to indent
     * - overwrite: Overwrite existing target file
     * - backup:    Backup existing target file
     *
     * @param array $options   Options
     * @param array $wsdlTypes WSDL types
     */
    public function __construct(array $options = array(), $wsdlTypes = array())
    {
        $this->wsdlTypes = $wsdlTypes;
        $this->options = array_merge($this->options, $options);
        $properties = array('extension', 'overwrite', 'backup');
        foreach ($options as $option => $value) {
            if (in_array($option, $properties)) {
                $this->$option = $value;
            }
        }
        $this->spaces = str_repeat(' ', $this->getOption('indent'));
    }

    /**
     * Get option value.
     *
     * @param string $name Name of option
     *
     * @return mixed
     */
    public function getOption($name)
    {
        return $this->options[$name];
    }

    /**
     * Generate and write class to disk for the given Data array.
     *
     * @param array(string=>mixed) $data            Data array
     * @param string               $targetDirectory Target directory
     *
     * @return string
     */
    public function writeClass($data, $targetDirectory)
    {
        $dir = $targetDirectory . DIRECTORY_SEPARATOR;
        if (!empty($data['namespace'])) {
            $dir .= str_replace('\\', DIRECTORY_SEPARATOR, $data['namespace']) . DIRECTORY_SEPARATOR;
        }
        $path = $dir . $this->createValidClassName($data['name'], $data['namespace']) . '.' . $this->extension;

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (!$this->overwrite && file_exists($path)) {
            throw new \RuntimeException("Target file exists (".$path.").");
        }

        if ($this->backup && file_exists($path)) {
            $backupPath = $path . "~";
            if (!copy($path, $backupPath)) {
                throw new \RuntimeException("Writing backup file failed.");
            }
        }

        file_put_contents($path, $this->generateClass($data) . "\n");

        return $path;
    }

    /**
     * Generate class.
     *
     * @param array(string=>mixed) $data Data array
     *
     * @return string
     */
    abstract public function generateClass($data);

    /**
     * Generate namespace.
     *
     * @param array(string=>mixed) $data Data array
     *
     * @return string
     */
    protected function generateNamespace($data)
    {
        if (!empty($data['namespace'])) {
            return 'namespace ' . $data['namespace'] . ";\n";
        }

        return '';
    }

    /**
     * Generate class docblock.
     *
     * @param array(string=>mixed) $data Data array
     *
     * @return string
     */
    protected function generateDocBlock($data)
    {
        $lines = array();
        $lines[] = '/**';
        $lines[] = ' * This class is generated from the following WSDL:';
        $lines[] = ' * ' . $data['wsdl'];
        if (!empty($data['documentation'])) {
            $lines[] = ' *';
            $width = 80-3;
            $break = "\n * ";
            $lines[] = ' * ' . wordwrap($data['documentation'], $width, $break);
        }
        $lines[] = ' */';

        return implode("\n", $lines);
    }

    /**
     * Create valid class name from desired class name according to
     * http://www.php.net/manual/en/language.oop5.basic.php.
     * Converts the two common reserved names list (to lizt) and class
     * (to clazz), prefixes invalid class names with '_' and also replaces
     * invalid characters with '_'.
     *
     * @param string $class     Class name
     * @param string $namespace Namespace
     *
     * @return string
     */
    protected function createValidClassName($class, $namespace)
    {
        if (in_array($class, self::$reservedKeywords)) {
            // handle common names
            if ('list' == $class) {
                $class = 'lizt';
            } elseif ('class' == $class) {
                $class = 'clazz';
            } else {
                $class = '_' . $class;
            }
        }

        if (empty($namespace) && (class_exists($class) || interface_exists($class))) {
            $class = '_' . $class;
        }

        if (!preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/', $class)) {
            $class = preg_replace('/^[^a-zA-Z_\x7f-\xff]/', '', $class);
            $class = preg_replace('/[^a-zA-Z0-9_\x7f-\xff]*/', '', $class);
        }

        return $class;
    }

    /**
     * Generate function arguments.
     *
     * @param array(string=>string) $args       List of arguments
     * @param bool                  $isRequired Required argument
     *
     * @return string
     */
    protected function generateFunctionArguments($args, $isRequired = true)
    {
        $parameters = array();
        foreach ($args as $name => $type) {
            if ('array' == $type ||
                !in_array($type, self::$phpTypes) &&
                (empty($this->wsdlTypes) || !empty($this->wsdlTypes[$type]['properties'][0]['name']))
            ) {
                $arg = ($type . ' $' . $name);
            } else {
                $arg = '$' . $name;
            }
            $parameters[] = ($arg . ($isRequired?'':' = null'));
        }

        return implode(', ', $parameters);
    }
}
