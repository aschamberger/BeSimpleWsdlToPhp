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

use BeSimple\SoapCommon\Helper;

/**
 * This class parses WSDL files and allows to retrieve types and operations.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 * @see http://www.w3.org/TR/wsdl
 */
class WsdlParser
{
    /**
     * DOMDocument WSDL file.
     *
     * @var \DOMDocument
     */
    private $domDocument;

    /**
     * DOMXPath WSDL file.
     *
     * @var \DOMXPath
     */
    private $domXpath;

    /**
     * WSDL file name.
     *
     * @var string
     */
    private $wsdlFile;

    /**
     * WSDL namespace of current WSDL file.
     *
     * @var string
     */
    private $wsdlSoapNamespace;

    /**
     * WSDL operations.
     *
     * @var array
     */
    protected $wsdlOperations = array();

    /**
     * WSDL types.
     *
     * @var array
     */
    protected $wsdlTypes = array();

    /**
     * WSDL errors.
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Constructor.
     *
     * @param string $wsdlFile    WSDL file name
     * @param string $soapVersion SOAP version constant
     */
    public function __construct($wsdlFile, $soapVersion)
    {
        if ($soapVersion == SOAP_1_1) {
            $this->wsdlSoapNamespace = Helper::NS_WSDL_SOAP_1_1;
        } else {
            $this->wsdlSoapNamespace = Helper::NS_WSDL_SOAP_1_2;
        }
        $this->loadXml($wsdlFile);
    }

    /**
     * @param \Exception $parent - previous exception, point to code position
     * @param string $message
     * @param string $wsdlFile
     * @param int $line
     */
    public function addError($parent, $message, $line = null, $wsdlFile = null)
    {
        if (null === $wsdlFile) {
            $wsdlFile = $this->wsdlFile;
        }
        $this->errors[] = new WsdlException($parent, $message, $wsdlFile, $line);
    }


    /**
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param string $wsdlFile
     * @return bool
     */
    protected function loadXml($wsdlFile)
    {
        static $files = array();
        if (in_array($wsdlFile, $files)) {
            return false;
        }
        $files[] = $wsdlFile;

        $this->domDocument = new \DOMDocument('1.0', 'utf-8');
        $this->domDocument->load($this->wsdlFile = $wsdlFile);
        $this->initXpathDom();
        return true;
    }

    protected function initXpathDom()
    {
        $this->domXpath = new \DOMXPath($this->domDocument);
        $this->domXpath->registerNamespace('wsdl', Helper::NS_WSDL);
        $this->domXpath->registerNamespace('xsd', Helper::NS_XML_SCHEMA);
        $this->domXpath->registerNamespace('mime', Helper::NS_WSDL_MIME);
        $this->domXpath->registerNamespace('soap', $this->wsdlSoapNamespace);
    }

    /**
     * @return array
     */
    public function getWsdlOperations()
    {
        if (empty($this->wsdlOperations)) {
            $this->parseWsdlOperations();
        }
        return $this->wsdlOperations;
    }

    /**
     * Extracts SOAP operations from WSDL file.
     *
     * Creating doc-lit WSDLs that "unwrap" nicely (http://pzf.fremantle.org/2007/05/handlign.html):
     * - SOAP binding style: document
     * - SOAP body use: literal
     * - Schema: elementFormDefault="qualified"
     * - Each message has a single part, named "parameters"
     * - Each message must refer to an element not a type
     * - The input element must be named the same as the OperationName
     * - The output element must be named OperationNameResponse
     */
    protected function parseWsdlOperations()
    {
        $queries = array(
//            '/wsdl:definitions/wsdl:binding/soap:binding[@style="document"]/../wsdl:operation/wsdl:input/soap:body[@use="literal"]/../..',
            '/wsdl:definitions/wsdl:portType/wsdl:operation'
        );

        foreach ($queries as $query) {

            $operations = $this->domXpath->query($query);

            $wsdlOperations = array();
            foreach ($operations as $operation) {
                $portType = $operation->parentNode->getAttribute('type');
                $wsdlOperations = array_merge($wsdlOperations, $this->resolveOperation($operation, $portType));
            }

            $this->wsdlOperations = array_merge($this->wsdlOperations, $wsdlOperations);
        }
    }

    /**
     * Resolve operation.
     *
     * @param \DOMElement $operation Soap operation
     * @param string $portType      Port type
     *
     * @return array()
     */
    private function resolveOperation($operation, $portType)
    {
        $operationName = $operation->getAttribute('name');
        list($prefix, $name) = $this->getTypeName($portType);
        $ns = $this->domDocument->lookupNamespaceURI($prefix);

        $wsdlOperations = array();
        $inputMessage = $operation->getElementsByTagName('input')->item(0)->getAttribute('message');
        $outputMessage = $operation->getElementsByTagName('output')->item(0)->getAttribute('message');

        list($prefix, $inputType) = $this->getTypeName($this->resolveMessageType($inputMessage));
        $tns = '';//$this->domDocument->lookupNamespaceURI($prefix).'/';
        $inputTypeNS = $tns.$inputType;

        list($prefix, $outputType) = $this->getTypeName($this->resolveMessageType($outputMessage));
        $tns = '';//$this->domDocument->lookupNamespaceURI($prefix).'/';
        $outputTypeNS = $tns.$outputType;

        $this->getWsdlTypes();
        $inputTypeWsdl = $this->wsdlTypes[$inputTypeNS];
        $outputTypeWsdl = $this->wsdlTypes[$outputTypeNS];

        $parameters = $this->getOperationParameters($this->wsdlTypes, $inputTypeWsdl['name']);

        $wsdlOperations[] = array(
            'name' => $operationName,
            'parameters' => $parameters,
            'wrapParameters' =>  (empty($inputTypeWsdl['namespace']) ?
                '' : $inputTypeWsdl['namespace'] . '\\') . $inputTypeWsdl['name'],
            'return' => (empty($outputTypeWsdl['namespace']) ?
                '' : $outputTypeWsdl['namespace'] . '\\') . $outputTypeWsdl['name'],
        );

        return $wsdlOperations;
    }

    /**
     * Get parameters of operation (resolving extended types).
     *
     * @param array  $wsdlTypes   WSDL types
     * @param string $inputTypeNS Type to resolve
     */
    private function getOperationParameters($wsdlTypes, $inputTypeNS)
    {
        $parameters = array();
        $inputTypeWsdl = $wsdlTypes[$inputTypeNS];
        foreach ($inputTypeWsdl['properties'] as $property) {
            $parameters[$property['name']] = $property['phpType'];
        }
        if (isset($inputTypeWsdl['parent'])) {
            $inputTypeNS = $inputTypeWsdl['parentXml'];
            $parameters = array_merge($parameters, $this->getOperationParameters($wsdlTypes, $inputTypeNS));
        }
        return $parameters;
    }

    /**
     * Resolve message type.
     *
     * @param string $messageType Message type
     *
     * @return string
     */
    private function resolveMessageType($messageType)
    {
        list($prefix, $name) = $this->getTypeName($messageType);
        $ns = $this->domDocument->lookupNamespaceURI($prefix);
        $query = '//wsdl:definitions[@targetNamespace="'. $ns .'"]/wsdl:message[@name="'. $name .'"]/wsdl:part';
        $parts = $this->domXpath->query($query);

        return $parts->item(0)->getAttribute('element');
    }

    /**
     * @param string $name
     * @param \DOMElement $element
     * @param string $attributeName
     * @param string $xmlSchemaPrefix
     *
     * @return array
     */
    protected function makeProperty($name, $element, $attributeName, $xmlSchemaPrefix)
    {
        return array(
            'name'     => $name,
            'phpType'  => $this->getPhpTypeForSchemaType(
                $element->getAttribute($attributeName),
                $xmlSchemaPrefix
            ),
            'wsdlType' => $element->getAttribute($attributeName),
            'restrictions' => array(),
            'isNull' => (bool)$element->getAttribute('nillable'),
        );
    }

    /**
     * @param \DOMElement $type
     * @param \DOMElement $schema
     * @param string $xmlSchemaPrefix
     * @param string $namespace
     *
     * @return array
     */
    protected function parseType($type, $schema, $xmlSchemaPrefix, $namespace)
    {
        $wsdlType = array(
            'wsdl'      => $this->wsdlFile,
            'namespace' => $namespace,
            'name'      => $type->getAttribute('name'),
        );

        $doc = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'documentation');
        if ($doc->length > 0) {
            $wsdlType['documentation'] = trim($doc->item(0)->nodeValue);
        }
        if (0 < $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'simpleContent')->length) {

            /** @var \DOMElement $extension */
            foreach ($type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'extension') as $extension) {
                $property = $this->makeProperty('_', $extension, 'base', $xmlSchemaPrefix);
                $wsdlType['properties'] = array($property);

                foreach ($type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'attribute') as $attribute) {
                    if ($attribute->hasAttribute('ref')) {
                        $property = $this->resolveAttribute($schema, $attribute, $xmlSchemaPrefix);
                    } else {
                        $property = $this->makeProperty(
                            $attribute->getAttribute('name'),
                            $attribute,
                            'type',
                            $xmlSchemaPrefix
                        );
                    }
                    $wsdlType['properties'][] = $property;
                }
            }
        } else {
            if (0 < $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'complexContent')->length) {
                $extension = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'extension');
                if ($extension->length > 0) {
                    $extension = $extension->item(0);
                    $wsdlType['parent'] = $this->getPhpTypeForSchemaType(
                        $extension->getAttribute('base'),
                        $xmlSchemaPrefix
                    );
                }
                $this->resolveElements($type, $wsdlType, $xmlSchemaPrefix);
            } else {
                $this->resolveElements($type, $wsdlType, $xmlSchemaPrefix);
            }
        }
        if (!empty($wsdlType['properties'])) {
            foreach ($wsdlType['properties'] as $property) {
                if (!empty($property['phpType']) && $wsdlType['name'] == $property['phpType']) {
                    $this->addError(
                        new Exception(),
                        "Type '{$wsdlType['name']}' have property '{$property['name']}' point to himself",
                        $type->getLineNo()
                    );
                }
            }
        }

        return $wsdlType;
    }

    /**
     * @param string $typeName
     *
     * @return array
     */
    protected function getTypeName($typeName)
    {
        $prefix = '';
        $names = explode(':', $typeName);
        if (2 == count($names)) {
            $prefix = $names[0];
            $name = $names[1];
        } else {
            $name = $names[0];
        }
        return array(
            $prefix,
            $name,
        );
    }

    /**
     * @param \DOMElement $schema
     *
     * @return array of Types
     */
    protected function parseImports($schema)
    {
        $wsdlTypes = array();
        $imports = $schema->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'import');

        if ($imports instanceof \DOMNodeList && 0 < $imports->length) {
            $oldWsdlFile = $this->wsdlFile;
            $oldDomDocument = clone $this->domDocument;
            /** @var \DOMElement $import */
            foreach ($imports as $import) {
                $location = $import->getAttribute('schemaLocation');
                if ($this->loadXml($location)) {
                    $wsdlTypes = array_merge($wsdlTypes, $this->parseWsdlTypes('/xsd:schema'));
                }
            }
            $this->domDocument = $oldDomDocument;
            $this->wsdlFile = $oldWsdlFile;
            $this->initXpathDom();
        }
        return $wsdlTypes;
    }

    /**
     * Recursive merge 2 or more arrays
     *
     * @return array
     */
    public static function arrayMergeRecursive()
    {
        if (func_num_args() < 2) {
            trigger_error(__FUNCTION__ . ' needs two or more array arguments', E_USER_WARNING);
            return null;
        }
        $arrays = func_get_args();
        $merged = array();
        while ($arrays) {
            $array = array_shift($arrays);
            if (!is_array($array)) {
                trigger_error(__FUNCTION__ . ' encountered a non array argument', E_USER_WARNING);
                return null;
            }
            if (!$array) continue;
            foreach ($array as $key => $value) {
                if (is_string($key)) {
                    if (is_array($value) && array_key_exists($key, $merged) && is_array($merged[$key])) {
                        $merged[$key] = static::arrayMergeRecursive($merged[$key], $value);
                    } else {
                        $merged[$key] = $value;
                    }
                } else {
                    $merged[] = $value;
                }
            }
        }
        return $merged;
    }

    /**
     * Extracts WSDL types from WSDL file.
     *
     * @return array(string=>mixed)
     */
    protected function parseWsdlTypes($query = '/wsdl:definitions/wsdl:types/xsd:schema')
    {
        $wsdlTypes = array();
        /** @var \DOMElement $schema */
        foreach ($this->domXpath->query($query) as $schema) {
            $wsdlTypes = array_merge($wsdlTypes, $this->parseImports($schema));

            $targetNamespace = $schema->getAttribute('targetNamespace');
            $namespace = $this->convertXmlNsToPhpNs($targetNamespace);

            if (false !== strpos($targetNamespace, 'http')) {
                $targetNamespace = '';
            } else {
                $targetNamespace .= '/';
            }

            $xmlSchemaPrefix = $schema->lookupPrefix(Helper::NS_XML_SCHEMA);

            /** @var \DOMElement $element */
            foreach ($this->domXpath->query($query . '/xsd:element') as $element) {
                $attrType = $element->getAttribute('type');
                list($prefix, $typeName) = $this->getTypeName($attrType);

                if (in_array($typeName, array_keys(XmlSchemaMapper::getAllTypes()))) {
                    continue;
                }

                $wsdlType = null;
                $attrName = $element->getAttribute('name');

                if ($element->hasChildNodes()) {
                    if ($subTypeName = $element->getAttribute('name')) {
                        $attrName = $subTypeName;
                    }
                    $wsdlType = $this->parseType(
                        $element,
                        $schema,
                        $xmlSchemaPrefix,
                        $namespace
                    );
                } else {
                    $types = $this->domXpath->query("//xsd:schema/xsd:complexType[@name=\"{$typeName}\"]");
                    if (0 == $types->length) {
                        $types = $this->domXpath->query("//xsd:schema/xsd:simpleType[@name=\"{$typeName}\"]");
                    }

                    if (1 < $types->length) {
                        $this->addError(
                            new Exception(),
                            "Element have more then one child #2",
                            $types->item(0)->parentNode->getLineNo()
                        );
                    }

                    /** @var $type \DOMElement */
                    if ($type = $types->item(0)) {
                        if ($subTypeName = $type->getAttribute('name')) {
                            $attrName = $subTypeName;
                        }
                        $wsdlType = $this->parseType(
                            $type,
                            $schema,
                            $xmlSchemaPrefix,
                            $namespace
                        );
                    }
                }
                if (!empty($wsdlType)) {
                    if (!empty($wsdlTypes[$targetNamespace . $attrName])) {
                        $this->addError(
                            new Exception(),
                            "Type: '{$targetNamespace}{$attrName}' in file '{$wsdlType['wsdl']}' already exist",
                            0,
                            $wsdlTypes[$targetNamespace . $attrName]['wsdl']
                        );
                        $wsdlTypes[$targetNamespace . $attrName] = $this->arrayMergeRecursive(
                            $wsdlTypes[$targetNamespace . $attrName],
                            $wsdlType
                        );
                    } else {
                        $wsdlTypes[$targetNamespace . $attrName] = $wsdlType;
                    }
                }
            }
        }
        return $wsdlTypes;
    }

    /**
     * @return array
     */
    public function getWsdlTypes()
    {
        if (empty($this->wsdlTypes)) {
            $this->wsdlTypes = $this->parseWsdlTypes();
        }
        return $this->wsdlTypes;
    }

    /**
     * Converts XML to PHP namespace.
     *
     * @param string $ns XML namespace.
     *
     * @return string
     */
    private function convertXmlNsToPhpNs($ns)
    {
        return '';
        $ns = rtrim($ns, '/');
        $nsArr = explode('/', $ns);
        $namespace = array_pop($nsArr);
        $namespace = str_replace('.', '\\', $namespace);
        $namespace = preg_replace('/[^\w\\\]/', '_', $namespace);
        $namespace = rtrim($namespace, '\\');

        return $namespace;
    }

    private function getPhpTypeForSchemaType($xmlSchemaType, $xmlSchemaPrefix)
    {

        // PHP type for complex type (=class name)...
        list($prefix, $type) = $this->getTypeName($xmlSchemaType);

        // XML schema types
        if ($xmlSchemaPrefix == substr($xmlSchemaType, 0, strlen($xmlSchemaPrefix))) {
            return XmlSchemaMapper::xmlSchemaToPhpType($xmlSchemaType, $xmlSchemaPrefix);
        }
        $ns = $this->domDocument->lookupNamespaceURI($prefix);
        $namespace = $this->convertXmlNsToPhpNs($ns);
        return ($namespace? $namespace . '\\' : '') . $type;
    }

    /**
     * @param \DOMElement $type
     * @param $property
     * @param null $xmlSchemaPrefix
     */
    private function resolveRestrictions($type, &$property, $xmlSchemaPrefix = null)
    {
        $restrictions = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'restriction');

        $property['restrictions'] = array();

        /** @var \DOMElement $restriction */
        foreach ($restrictions as $restriction) {
            $enumeration = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'enumeration');
            if ($enumeration->length > 0) {
                $property['enum'] = array();
                foreach ($enumeration as $enum) {
                    $property['enum'][] = $enum->getAttribute('value');
                }
            } elseif (null !== $xmlSchemaPrefix) {
                $property = $this->makeProperty('_', $restriction, 'base', $xmlSchemaPrefix);
            }

            $property['wsdlType'] = $restriction->getAttribute('base');

            /** @var \DOMElement $restriction */
            foreach ($restriction->childNodes as $rule) {
                if ($rule instanceof \DOMElement) {
                    $property['restrictions'][$rule->localName] = $rule->getAttribute('value');
                }
            }
        }
    }

    private function resolveAttribute($schema, $attribute, $xmlSchemaPrefix)
    {
        list($prefix, $name) = $this->getTypeName($attribute->getAttribute('ref'));
        $ns = $schema->lookupNamespaceURI($prefix);
        $query = '//xsd:schema[@targetNamespace="'. $ns .'"]/xsd:attribute[@name="'. $name .'"]';
        $attributeRef = $this->domXpath->query($query, $schema);

        if ($attributeRef->length > 0) {
            $attributeElement = $attributeRef->item(0);

            $restriction = $attributeElement->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'restriction')->item(0);

            $property = $this->makeProperty(
                $attributeElement->getAttribute('name'),
                $restriction,
                'base',
                $xmlSchemaPrefix
            );

            $this->resolveRestrictions($attributeElement, $property);

            return $property;
        }
    }

    /**
     * @param \DOMElement $type
     * @param array $wsdlType
     * @param string $xmlSchemaPrefix
     */
    private function resolveElements($type, &$wsdlType, $xmlSchemaPrefix)
    {
        $elements = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'element');

        if ($elements->length > 0) {
            $wsdlType['properties'] = array();
            foreach ($elements as $element) {

//                var_dump($element);
                $property = $this->makeProperty($element->getAttribute('name'), $element, 'type', $xmlSchemaPrefix);

                if ($element->hasAttribute('maxOccurs') &&
                    ($element->getAttribute('maxOccurs') > 1 ||
                        'unbounded' == $element->getAttribute('maxOccurs'))
                ) {
                    $property['phpType'] = 'array(' . $property['phpType'] . ')';
                }

                $this->resolveRestrictions($type, $property);

                $wsdlType['properties'][] = $property;
            }
        } elseif ($type->hasChildNodes()) {
            $this->resolveRestrictions($type, $property, $xmlSchemaPrefix);;

            $wsdlType['properties'][] = $property;
        }
    }
}
