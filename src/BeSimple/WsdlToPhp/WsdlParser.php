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
 * @see    http://www.w3.org/TR/wsdl
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
     * @var array(WsdlParserError)
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

    /**
     * Init XPATH object.
     */
    protected function initXpathDom()
    {
        $this->domXpath = new \DOMXPath($this->domDocument);
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
     * @param \DOMElement $operation Soap operation
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
        $tns = rtrim($this->domDocument->lookupNamespaceURI($prefix), '/').'/';
        $inputTypeNS = $tns.$inputType;

        list($prefix, $outputType) = $this->getTypeName($this->resolveMessageType($outputMessage));
        $tns = rtrim($this->domDocument->lookupNamespaceURI($prefix), '/').'/';
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
     * Make property.
     *
     * @param string      $name            Name
     * @param \DOMElement $element         Element
     * @param string      $attributeName   Attribute name
     * @param string      $xmlSchemaPrefix XML schema prefix
     * @param string      $namespace       Current target namespace
     *
     * @return array
     */
    protected function makeProperty($name, $element, $attributeName, $xmlSchemaPrefix, $namespace=null)
    {
        return array(
            'name'     => $name,
            'phpType'  => $this->getPhpTypeForSchemaType(
                $element->getAttribute($attributeName),
                $xmlSchemaPrefix,
                $namespace
            ),
            'wsdlType' => $element->getAttribute($attributeName),
            'restrictions' => array(),
            'isNull' => (bool) $element->getAttribute('nillable'),
        );
    }

    /**
     * Parse type.
     *
     * @param \DOMElement $type            Type DOMElement
     * @param \DOMElement $schema          Schema DOMElement
     * @param string      $xmlSchemaPrefix XML schema prefix
     * @param string      $namespace       XML namespace
     *
     * @return array
     */
    protected function parseType(\DOMElement $type, \DOMElement $schema, $xmlSchemaPrefix, $namespace)
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
                $property = $this->makeProperty('_', $extension, 'base', $xmlSchemaPrefix, $namespace);
                $wsdlType['properties'] = array($property);

                foreach ($type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'attribute') as $attribute) {
                    if ($attribute->hasAttribute('ref')) {
                        $property = $this->resolveAttribute($schema, $attribute, $xmlSchemaPrefix, $namespace);
                    } else {
                        $property = $this->makeProperty(
                            $attribute->getAttribute('name'),
                            $attribute,
                            'type',
                            $xmlSchemaPrefix,
                            $namespace
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
                        $xmlSchemaPrefix,
                        $namespace
                    );
                }
                $this->resolveElements($type, $wsdlType, $xmlSchemaPrefix, $namespace);
            } else {
                $this->resolveElements($type, $wsdlType, $xmlSchemaPrefix, $namespace);
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
     * Parse XML imports.
     *
     * @param \DOMElement $schema Schema DOMElement
     *
     * @return array of Types
     */
    protected function parseImports(\DOMElement $schema)
    {
        $wsdlTypes = array();
        $imports = $schema->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'import');

        if ($imports instanceof \DOMNodeList && 0 < $imports->length) {
            $oldWsdlFile = $this->wsdlFile;
            $oldDomDocument = clone $this->domDocument;
            /** @var \DOMElement $import */
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
     * Recursive merge 2 or more arrays
     *
     * @see: http://www.php.net/manual/de/function.array-merge-recursive.php#104145
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
            if (!$array) {
                continue;
            }
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
     * @param string $query XPATH query
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
            $xmlSchemaPrefix = $schema->lookupPrefix(Helper::NS_XML_SCHEMA);

            /** @var \DOMElement $element */
            foreach ($this->domXpath->query("//xsd:schema[@targetNamespace=\"{$targetNamespace}\"]/xsd:element") as $element) {
                $attrType = $element->getAttribute('type');
                list($prefix, $typeName) = $this->getTypeName($attrType);

                if (in_array($typeName, array_keys(XmlSchemaMapper::getAllTypes()))) {
                    continue;
                }

                $wsdlType = null;
                $attrName = $element->getAttribute('name');

                if ($element->hasChildNodes()) {
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
                            "Element have more then one child #2",
                            $types->item(0)->parentNode->getLineNo()
                        );
                    }

                    /** @var $type \DOMElement */
                    if ($type = $types->item(0)) {
                        $wsdlType = $this->parseType(
                            $type,
                            $schema,
                            $xmlSchemaPrefix,
                            $namespace
                        );
                        $wsdlType['name'] = $attrName;
                    }
                }

                $wsdlTypeName = rtrim($targetNamespace, '/').'/'.$attrName;

                if (!empty($wsdlType)) {
                    if (!empty($wsdlTypes[$wsdlTypeName])) {
                        $this->addError(
                            "Type: '{$wsdlTypes[$wsdlTypeName]['namespace']}\{$wsdlTypes[$wsdlTypeName]['name']}' in file '{$wsdlType['wsdl']}' already exist",
                            0,
                            $wsdlTypes[$wsdlTypeName]['wsdl']
                        );
                        $wsdlTypes[$wsdlTypeName] = $this->arrayMergeRecursive(
                            $wsdlTypes[$wsdlTypeName],
                            $wsdlType
                        );
                    } else {
                        $wsdlTypes[$wsdlTypeName] = $wsdlType;
                    }
                }
            }
        }

        return $wsdlTypes;
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
     * Converts XML to PHP namespace.
     *
     * @param string $ns XML namespace.
     *
     * @return string
     */
    private function convertXmlNsToPhpNs($ns)
    {
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
     * Get corresponding PHP type for XML schema type.
     *
     * @param string $xmlSchemaType   XML schema type
     * @param string $xmlSchemaPrefix XML schema prefix
     * @param string $namespace       Current target namespace
     *
     * @return string
     */
    private function getPhpTypeForSchemaType($xmlSchemaType, $xmlSchemaPrefix, $namespace)
    {
        // PHP type for complex type (=class name)...
        list($prefix, $type) = $this->getTypeName($xmlSchemaType);

        // XML schema types
        if ($xmlSchemaPrefix == $prefix) {
            return XmlSchemaMapper::xmlSchemaToPhpType($xmlSchemaType, $xmlSchemaPrefix);
        }

        if ('tns' != $prefix) {
            $ns = $this->domDocument->lookupNamespaceURI($prefix);
            $namespace = $this->convertXmlNsToPhpNs($ns);
        }

        return ($namespace? $namespace . '\\' : '') . $type;
    }

    /**
     * Resolve XML schema element restrictions.
     *
     * @param \DOMElement $type            Type \DOMElement
     * @param array       &$property       Property array
     * @param string      $xmlSchemaPrefix XML schema prefix
     */
    private function resolveRestrictions(\DOMElement $type, &$property, $xmlSchemaPrefix = null)
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

    /**
     * Resolve attribute.
     *
     * @param string $schema          Schema \DOMElement
     * @param string $attribute       Attribute \DOMElement
     * @param string $xmlSchemaPrefix XML schema prefix
     * @param string $namespace       Current target namespace
     *
     * @return array()
     */
    private function resolveAttribute(\DOMElement $schema, \DOMElement $attribute, $xmlSchemaPrefix, $namespace)
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
                $xmlSchemaPrefix,
                $namespace
            );

            $this->resolveRestrictions($attributeElement, $property);

            return $property;
        }
    }

    /**
     * Resolve elements.
     *
     * @param \DOMElement $type            Type \DOMElement
     * @param array       &$wsdlType       WSDL type
     * @param string      $xmlSchemaPrefix XML schema prefix
     * @param string      $namespace       Current target namespace
     */
    private function resolveElements($type, &$wsdlType, $xmlSchemaPrefix, $namespace)
    {
        $elements = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'element');

        if ($elements->length > 0) {
            $wsdlType['properties'] = array();
            foreach ($elements as $element) {
                $property = $this->makeProperty($element->getAttribute('name'), $element, 'type', $xmlSchemaPrefix, $namespace);

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
