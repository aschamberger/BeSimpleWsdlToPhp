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
use \DOMXPath;
use \DOMDocument;
use \DOMElement;
use \DOMNodeList;

/**
 * This class parses WSDL files and allows to retrieve types and operations.
 *
 * @author Andreas Schamberger <mail@andreass.net>
 * @see    http://www.w3.org/TR/wsdl
 */
class WsdlParser
{
    /**
     * DOMDocument WSDL file.
     *
     * @var DOMDocument
     */
    private $domDocument;

    /**
     * DOMXPath WSDL file.
     *
     * @var DOMXPath
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
     * @var array(WsdlParserError)
     */
    protected $errors = array();

    /**
     * Configuration options
     *
     * @var array
     */
    protected $options = array(
        'wsdl2java_style' => true,
        'empty_parameter_name' => '_',
    );

    /**
     * Loaded files
     *
     * @var array
     */
    private $files = array();

    /**
     * Constructor.
     *
     * @param string $wsdlFile    WSDL file name
     * @param string $soapVersion SOAP version constant
     */
    public function __construct($wsdlFile, $soapVersion, array $options = null)
    {
        if ($soapVersion == SOAP_1_1) {
            $this->wsdlSoapNamespace = Helper::NS_WSDL_SOAP_1_1;
        } else {
            $this->wsdlSoapNamespace = Helper::NS_WSDL_SOAP_1_2;
        }
        $this->loadXml($wsdlFile);

        if ($options) {
            $this->options = array_merge($this->options, $options);
        }
    }

    /**
     * Add WSDL parsing error.
     *
     * @param string $message  Error message
     * @param int    $line     Error line number
     * @param string $wsdlFile WSDL file name
     */
    public function addError($message, $line = null, $wsdlFile = null)
    {
        if (null === $wsdlFile) {
            $wsdlFile = $this->wsdlFile;
        }
        $this->errors[] = new WsdlParserError($message, $wsdlFile, $line);
    }

    /**
     * Has the WSDL parser instance any errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * Get list of WSDL parser errors.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Load WSDL XML file.
     *
     * @param string $wsdlFile WSDL file name
     *
     * @return bool
     */
    protected function loadXml($wsdlFile)
    {
        if (in_array($wsdlFile, $this->files)) {
            return false;
        }
        $this->files[] = $wsdlFile;

        $this->domDocument = new DOMDocument('1.0', 'utf-8');
        $this->domDocument->load($this->wsdlFile = $wsdlFile);
        $this->initXpathDom();

        return true;
    }

    /**
     * Init XPATH object.
     */
    protected function initXpathDom()
    {
        $this->domXpath = new DOMXPath($this->domDocument);
        $this->domXpath->registerNamespace('wsdl', Helper::NS_WSDL);
        $this->domXpath->registerNamespace('xsd', Helper::NS_XML_SCHEMA);
        $this->domXpath->registerNamespace('mime', Helper::NS_WSDL_MIME);
        $this->domXpath->registerNamespace('soap', $this->wsdlSoapNamespace);
    }

    /**
     * Get WSDL operations.
     *
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
        $query = '/wsdl:definitions/wsdl:portType/wsdl:operation';
        $operations = $this->domXpath->query($query);

        foreach ($operations as $operation) {
            $portType = $operation->parentNode->getAttribute('type');
            $this->wsdlOperations[] = $this->resolveOperation($operation, $portType);
        }
    }

    /**
     * Resolve operation.
     *
     * @param DOMElement $operation Soap operation
     * @param string      $portType  Port type
     *
     * @return array()
     */
    private function resolveOperation($operation, $portType)
    {
        $operationName = $operation->getAttribute('name');
        list($prefix, $name) = $this->getTypeName($portType);
        $ns = $this->domDocument->lookupNamespaceURI($prefix);

        $inputMessage = $operation->getElementsByTagName('input')->item(0)->getAttribute('message');
        $outputMessage = $operation->getElementsByTagName('output')->item(0)->getAttribute('message');

        list($prefix, $inputType) = $this->getTypeName($this->resolveMessageType($inputMessage));
        $tns = $this->trimTypeNs($this->domDocument->lookupNamespaceURI($prefix));
        $inputTypeNS = $tns.$inputType;

        list($prefix, $outputType) = $this->getTypeName($this->resolveMessageType($outputMessage));
        $tns = $this->trimTypeNs($this->domDocument->lookupNamespaceURI($prefix));
        $outputTypeNS = $tns.$outputType;

        $this->getWsdlTypes();
        $inputTypeWsdl = $this->wsdlTypes[$inputTypeNS];
        $outputTypeWsdl = $this->wsdlTypes[$outputTypeNS];

        $parameters = $this->getOperationParameters($this->wsdlTypes, $inputTypeNS);

        return array(
            'name' => $operationName,
            'parameters' => $parameters,
            'wrapParameters' =>  (empty($inputTypeWsdl['namespace']) ?
                '' : $inputTypeWsdl['namespace'] . '\\') . $inputTypeWsdl['name'],
            'return' => (empty($outputTypeWsdl['namespace']) ?
                '' : $outputTypeWsdl['namespace'] . '\\') . $outputTypeWsdl['name'],
        );
    }

    /**
     * Get parameters of operation (resolving extended types).
     *
     * @param array  $wsdlTypes   WSDL types
     * @param string $inputTypeNS Type to resolve
     *
     * @return array
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
     * Get list of WSDL types.
     *
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
     * Add type to the $typeList
     *
     * @param array $typeList
     * @param string $newTypeName
     * @param array $newType
     */
    protected function addNewTypeIntoList(&$typeList, $newTypeName, $newType)
    {
        if (!empty($typeList[$newTypeName])) {
            $this->addError(
                "Type: '{$newTypeName}' in file '{$newType['wsdl']}' already exist",
                0,
                $typeList[$newTypeName]['wsdl']
            );
            $typeList[$newTypeName] = $this->arrayMergeRecursive(
                $typeList[$newTypeName],
                $newType
            );
        } else {
            $typeList[$newTypeName] = $newType;
        }
    }

    /**
     * Extracts WSDL types from WSDL file.
     *
     * @param string $query XPATH query
     *
     * @return array(string=>mixed)
     */
    protected function parseWsdlTypes($query = '/wsdl:definitions/wsdl:types/xsd:schema')
    {
        $wsdlTypes = array();

        /** @var DOMElement $schema */
        foreach ($this->domXpath->query($query) as $schema) {
            $wsdlTypes = array_merge($wsdlTypes, $this->parseImports($schema));

            $targetNamespace = $schema->getAttribute('targetNamespace');
            $namespace = $this->convertXmlNsToPhpNs($targetNamespace);
            $xmlSchemaPrefix = $schema->lookupPrefix(Helper::NS_XML_SCHEMA);

            $elements = $this->domXpath->query("//xsd:schema[@targetNamespace=\"{$targetNamespace}\"]/xsd:element");
            /** @var DOMElement $element */
            foreach ($elements as $element) {
                $attrType = $element->getAttribute('type');
                list($prefix, $typeName) = $this->getTypeName($attrType);

                $wsdlType = null;
                $attrName = $element->getAttribute('name');
                $wsdlTypeName = $this->trimTypeNs($targetNamespace).$attrName;

                // element can be declared directly
                if ($element->hasChildNodes()) {
                    $wsdlType = $this->parseType(
                        $element,
                        $schema,
                        $namespace
                    );
                    $this->addNewTypeIntoList($wsdlTypes, $wsdlTypeName, $wsdlType);
                // element can have a type attribute that refers to the name of the complex type to use
                } else {
                    // TODO only create classes for non default XML schema types?
                    if ($prefix == $xmlSchemaPrefix &&
                        in_array($typeName, array_keys(XmlSchemaMapper::getAllTypes()))
                    ) {
                        continue;
                    } else {
                        $wsdlType = array(
                            'wsdl' => $this->wsdlFile,
                            'namespace' => $namespace,
                            'name' => $attrName,
                            'parent' => $this->getPhpTypeForSchemaType($element->getAttribute('type'), $schema),
                            'properties' => array(),
                        );
                        $this->addNewTypeIntoList($wsdlTypes, $wsdlTypeName, $wsdlType);
                    }
                }
            }

            $complexTypes = $this->domXpath->query(
                "//xsd:schema[@targetNamespace=\"{$targetNamespace}\"]/xsd:complexType"
            );
            /** @var DOMElement $element */
            foreach ($complexTypes as $complexType) {
                $wsdlType = null;
                $wsdlTypeName = $this->trimTypeNs($targetNamespace).$complexType->getAttribute('name');
                $wsdlType = $this->parseType(
                    $complexType,
                    $schema,
                    $namespace
                );
                $this->addNewTypeIntoList($wsdlTypes, $wsdlTypeName, $wsdlType);
            }

            $simpleTypes = $this->domXpath->query(
                "//xsd:schema[@targetNamespace=\"{$targetNamespace}\"]/xsd:simpleType"
            );
            /** @var DOMElement $element */
            foreach ($simpleTypes as $simpleType) {
                $wsdlType = null;
                $wsdlTypeName = $this->trimTypeNs($targetNamespace).$simpleType->getAttribute('name');
                $wsdlType = $this->parseType(
                    $simpleType,
                    $schema,
                    $namespace
                );
                $this->addNewTypeIntoList($wsdlTypes, $wsdlTypeName, $wsdlType);
            }
        }

        return $wsdlTypes;
    }

    /**
     * Parse XML imports.
     *
     * @param DOMElement $schema Schema DOMElement
     *
     * @return array of Types
     */
    protected function parseImports(DOMElement $schema)
    {
        $wsdlTypes = array();
        $imports = $schema->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'import');

        if ($imports instanceof DOMNodeList && 0 < $imports->length) {
            $oldWsdlFile = $this->wsdlFile;
            $oldDomDocument = clone $this->domDocument;
            /** @var DOMElement $import */
            foreach ($imports as $import) {
                $location = $import->getAttribute('schemaLocation');
                if ("" != $location && $this->loadXml($location)) {
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
     * Parse type.
     *
     * @param DOMElement $type      Type DOMElement
     * @param DOMElement $schema    Schema DOMElement
     * @param string      $namespace Namespace
     *
     * @return array
     */
    protected function parseType(DOMElement $type, DOMElement $schema, $namespace)
    {
        $wsdlType = array(
            'wsdl'      => $this->wsdlFile,
            'namespace' => $namespace,
            'name'      => $type->getAttribute('name'),
        );

        $doc = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'documentation')->item(0);
        if (null != $doc) {
            $wsdlType['documentation'] = trim($doc->nodeValue);
        }

        if ($type->localName == 'simpleType') {
            $simpleType = $type;
        } else {
            $simpleType = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'simpleType')->item(0);
        }

        if (null !== $simpleType) {
            $property = array('name' => $this->options['empty_parameter_name']);
            $this->resolveRestrictions($simpleType, $property);
            $property['phpType'] = $this->getPhpTypeForSchemaType($property['wsdlType'], $schema);
            $property['isNull'] = (bool) $type->getAttribute('nillable');
            $wsdlType['properties'] = array($property);
        } else {
            $simpleContent = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'simpleContent');
            if (0 < $simpleContent->length) {
                $extension = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'extension')->item(0);
                if (null !== $extension) {
                    $property = $this->makeProperty(
                        $this->options['empty_parameter_name'],
                        $extension,
                        'base',
                        $schema
                    );
                    $wsdlType['properties'] = array($property);
                }

                foreach ($type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'attribute') as $attribute) {
                    if ($attribute->hasAttribute('ref')) {
                        $property = $this->resolveAttribute($schema, $attribute, $schema);
                    } else {
                        $property = $this->makeProperty(
                            $attribute->getAttribute('name'),
                            $attribute,
                            'type',
                            $schema
                        );
                    }
                    $wsdlType['properties'][] = $property;
                }
            } else {
                $complexContent = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'complexContent');
                if (0 < $complexContent->length) {
                    $extension = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'extension')->item(0);
                    if (null !== $extension) {
                        $wsdlType['parent'] = $this->getPhpTypeForSchemaType(
                            $extension->getAttribute('base'),
                            $schema
                        );
                    }
                    $this->resolveElements($type, $wsdlType, $schema);
                } else {
                    $this->resolveElements($type, $wsdlType, $schema);
                }
            }
        }
        if (!empty($wsdlType['properties'])) {
            foreach ($wsdlType['properties'] as $property) {
                if (!empty($property['phpType']) && $wsdlType['name'] == $property['phpType']) {
                    $this->addError(
                        "Type '{$wsdlType['name']}' have property '{$property['name']}' point to himself",
                        $type->getLineNo()
                    );
                }
            }
        }

        return $wsdlType;
    }

    /**
     * Make property.
     *
     * @param string      $name          Name
     * @param DOMElement $element       Element
     * @param string      $attributeName Attribute name
     * @param DOMElement $schema        XML schema
     *
     * @return array
     */
    protected function makeProperty($name, $element, $attributeName, $schema)
    {
        return array(
            'name'     => $name,
            'phpType'  => $this->getPhpTypeForSchemaType(
                $element->getAttribute($attributeName),
                $schema
            ),
            'wsdlType' => $element->getAttribute($attributeName),
            'restrictions' => array(),
            'isNull' => (bool) $element->getAttribute('nillable'),
        );
    }

    /**
     * Resolve XML schema element restrictions.
     *
     * @param DOMElement $type      Type DOMElement
     * @param array       &$property Property array
     */
    private function resolveRestrictions(DOMElement $type, &$property)
    {
        $property['restrictions'] = array();

        $restrictions = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'restriction');
        if ($restrictions->length > 0) {
            /** @var DOMElement $restriction */
            foreach ($restrictions as $restriction) {
                $enumeration = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'enumeration');
                if ($enumeration->length > 0) {
                    $property['enum'] = array();
                    foreach ($enumeration as $enum) {
                        $property['enum'][] = $enum->getAttribute('value');
                    }
                }

                $property['wsdlType'] = $restriction->getAttribute('base');

                /** @var DOMElement $restriction */
                foreach ($restriction->childNodes as $rule) {
                    if ($rule instanceof DOMElement && 'enumeration' != $rule->localName) {
                        $property['restrictions'][$rule->localName] = $rule->getAttribute('value');
                    }
                }
            }
        }
    }

    /**
     * Resolve attribute.
     *
     * @param string $schema    Schema DOMElement
     * @param string $attribute Attribute DOMElement
     *
     * @return array()
     */
    private function resolveAttribute(DOMElement $schema, DOMElement $attribute)
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
                $schema
            );

            $this->resolveRestrictions($attributeElement, $property);

            return $property;
        }
    }

    /**
     * Resolve elements.
     *
     * @param DOMElement $type      Type DOMElement
     * @param array       &$wsdlType WSDL type
     * @param DOMElement $schema    XML schema
     */
    private function resolveElements($type, &$wsdlType, $schema)
    {
        $complexTypes = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'complexType');

        if ($complexTypes->length > 1) {
            $typeName = $type->getAttribute('name');
            $this->addError(
                "Nested complexType element declaration in XML schema for type '{$typeName}' not supported.",
                $type->getLineNo()
            );
            return;
        }

        $elements = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'element');

        if ($elements->length > 0) {
            $wsdlType['properties'] = array();
            foreach ($elements as $element) {
                $property = $this->makeProperty($element->getAttribute('name'), $element, 'type', $schema);

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
            $property = array();
            $this->resolveRestrictions($type, $property);

            $wsdlType['properties'][] = $property;
        }
    }

    /**
     * Get type name.
     *
     * @param string $typeName Type name
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
     * Converts XML to PHP namespace, java style.
     *
     * @param string $ns XML namespace.
     *
     * @return string
     */
    private function convertXmlNsToPhpNs($ns)
    {
        // Disable url to namespace conversion by --wsdl2java_style parameter
        if (!$this->options['wsdl2java_style']) {
            return '';
        }

        // java style http://soap.domain.tld/myservice -> tld\domain\soap\myservice
        if (false !== strpos($ns, 'http')) {
            $url = parse_url($ns);
            $host = array_reverse(explode('.', $url['host']));
            $namespace = implode('\\', $host).str_replace('/', '\\', $url['path']);
        } else {
            $namespace = str_replace('/', '\\', $ns);
        }

        $namespace = preg_replace('/[^\w\\\]/', '_', $namespace);
        $namespace = rtrim($namespace, '\\');

        return $namespace;
    }

    /**
     * Trim namespace (url)
     *
     * @param string $ns
     *
     * @return string
     */
    private function trimTypeNs($ns)
    {
        // Disable url to namespace conversion by --wsdl2java_style parameter
        if (!$this->options['wsdl2java_style']) {
            return '';
        }

        return rtrim($ns, '/') . '/';
    }

    /**
     * Get corresponding PHP type for XML schema type.
     *
     * @param string      $xmlSchemaType XML schema type
     * @param DOMElement $schema        XML schema
     *
     * @return string
     */
    private function getPhpTypeForSchemaType($xmlSchemaType, $schema)
    {
        // PHP type for complex type (=class name)...
        list($prefix, $type) = $this->getTypeName($xmlSchemaType);

        // XML schema types
        $xmlSchemaPrefix = $schema->lookupPrefix(Helper::NS_XML_SCHEMA);
        if ($xmlSchemaPrefix == $prefix) {
            return XmlSchemaMapper::xmlSchemaToPhpType($xmlSchemaType, $xmlSchemaPrefix);
        }

        if ('tns' != $prefix) {
            $ns = $schema->lookupNamespaceURI($prefix);
            $namespace = $this->convertXmlNsToPhpNs($ns);
        } else {
            $targetNamespace = $schema->getAttribute('targetNamespace');
            $namespace = $this->convertXmlNsToPhpNs($targetNamespace);
        }

        return ($namespace? $namespace . '\\' : '') . $type;
    }
}
