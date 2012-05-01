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
     * Constructor.
     *
     * @param string $wsdlFile    WSDL file name
     * @param string $soapVersion SOAP version constant
     */
    public function __construct($wsdlFile, $soapVersion)
    {
        $this->wsdlFile = $wsdlFile;
        if ($soapVersion == SOAP_1_1) {
            $this->wsdlSoapNamespace = Helper::NS_WSDL_SOAP_1_1;
        } else {
            $this->wsdlSoapNamespace = Helper::NS_WSDL_SOAP_1_2;
        }

        $this->domDocument = new \DOMDocument('1.0', 'utf-8');
        $this->domDocument->load($this->wsdlFile);
        $this->domXpath = new \DOMXPath($this->domDocument);
        $this->domXpath->registerNamespace('wsdl', Helper::NS_WSDL);
        $this->domXpath->registerNamespace('xsd', Helper::NS_XML_SCHEMA);
        $this->domXpath->registerNamespace('mime', Helper::NS_WSDL_MIME);
        $this->domXpath->registerNamespace('soap', $this->wsdlSoapNamespace);
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
     *
     * @return array(string=>mixed)
     */
    public function getOperations()
    {
        $query = '/wsdl:definitions/wsdl:binding/soap:binding[@style="document"]/../wsdl:operation/wsdl:input/soap:body[@use="literal"]/../..';
        $operations = $this->domXpath->query($query);

        $wsdlOperations = array();
        foreach ($operations as $operation) {
            $operationName = $operation->getAttribute('name');
            $portType = $operation->parentNode->getAttribute('type');
            $wsdlOperations[] = $this->resolveOperation($operationName, $portType);
        }

        return $wsdlOperations;
    }

    /**
     * Resolve operation.
     *
     * @param string $operationName Soap operation name
     * @param string $portType      Port type
     *
     * @return array()
     */
    private function resolveOperation($operationName, $portType)
    {
        list($prefix, $name) = explode(':', $portType);
        $ns = $this->domDocument->lookupNamespaceURI($prefix);
        $query = '//wsdl:definitions[@targetNamespace="'. $ns .'"]/wsdl:portType[@name="'. $name .'"]/wsdl:operation[@name="'. $operationName .'"]';
        $operation = $this->domXpath->query($query)->item(0);

        $inputMessage = $operation->getElementsByTagName('input')->item(0)->getAttribute('message');
        $outputMessage = $operation->getElementsByTagName('output')->item(0)->getAttribute('message');

        list($prefix, $inputType) = explode(':', $this->resolveMessageType($inputMessage));
        $tns = $this->domDocument->lookupNamespaceURI($prefix);
        $inputTypeNS = $tns.'/'.$inputType;

        list($prefix, $outputType) = explode(':', $this->resolveMessageType($outputMessage));
        $tns = $this->domDocument->lookupNamespaceURI($prefix);
        $outputTypeNS = $tns.'/'.$outputType;

        $wsdlTypes = $this->getWsdlTypes();
        $inputTypeWsdl = $wsdlTypes[$inputTypeNS];
        $outputTypeWsdl = $wsdlTypes[$outputTypeNS];

        $parameters = array();
        $this->getOperationParameters($parameters, $wsdlTypes, $inputTypeNS);

        $wsdlOperation = array(
            'name' => $operationName,
            'parameters' => $parameters,
            'wrapParameters' => '\\'.$inputTypeWsdl['namespace'].'\\'.$inputTypeWsdl['name'],
            'return' => '\\'.$outputTypeWsdl['namespace'].'\\'.$outputTypeWsdl['name'],
        );

        return $wsdlOperation;
    }

    /**
     * Get parameters of operation (resolving extended types).
     *
     * @param array  &$parameters Parameters array
     * @param array  $wsdlTypes   WSDL types
     * @param string $inputTypeNS Type to resolve
     */
    private function getOperationParameters(&$parameters, $wsdlTypes, $inputTypeNS)
    {
        $inputTypeWsdl = $wsdlTypes[$inputTypeNS];
        foreach ($inputTypeWsdl['properties'] as $property) {
            $parameters[$property['name']] = $property['phpType'];
        }
        if (isset($inputTypeWsdl['parent'])) {
            $inputTypeNS = $inputTypeWsdl['parentXml'];
            $this->getOperationParameters($parameters, $wsdlTypes, $inputTypeNS);
        }
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
        list($prefix, $name) = explode(':', $messageType);
        $ns = $this->domDocument->lookupNamespaceURI($prefix);
        $query = '//wsdl:definitions[@targetNamespace="'. $ns .'"]/wsdl:message[@name="'. $name .'"]/wsdl:part';
        $parts = $this->domXpath->query($query);

        return $parts->item(0)->getAttribute('element');
    }

    /**
     * Extracts WSDL types from WSDL file.
     *
     * @return array(string=>mixed)
     */
    public function getWsdlTypes()
    {
        $query = '/wsdl:definitions/wsdl:types/xsd:schema';
        $schemas = $this->domXpath->query($query);

        $wsdlTypes = array();

        foreach ($schemas as $schema) {
            $targetNamespace = $schema->getAttribute('targetNamespace');
            $namespace = $this->convertXmlNsToPhpNs($targetNamespace);

            $xmlSchemaPrefix = $schema->lookupPrefix(Helper::NS_XML_SCHEMA);

            $types = $schema->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'complexType');

            foreach ($types as $type) {
                $wsdlType = array(
                    'wsdl'      => $this->wsdlFile,
                    'namespace' => $namespace,
                    'name'      => $type->getAttribute('name'),
                );

                $doc = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'documentation');
                if ($doc->length > 0) {
                    $wsdlType['documentation'] = trim($doc->item(0)->nodeValue);
                }

                $simpleContent = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'simpleContent');
                $complexContent = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'complexContent');
                if ($simpleContent->length > 0) {
                    $extension = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'extension');
                    if ($extension->length > 0) {
                        $extension = $extension->item(0);
                        $wsdlType['properties'] = array();
                        $property = array(
                            'name'     => '_',
                            'phpType'  => $this->getPhpTypeForSchemaType($extension->getAttribute('base'), $xmlSchemaPrefix),
                            'wsdlType' => $extension->getAttribute('base'),
                        );
                        $wsdlType['properties'][] = $property;

                        $attributes = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'attribute');

                        if ($attributes->length > 0) {
                            foreach ($attributes as $attribute) {
                                if ($attribute->hasAttribute('ref')) {
                                    $property = $this->resolveAttribute($schema, $attribute, $xmlSchemaPrefix);
                                } else {
                                    $property = array(
                                        'name'     => $attribute->getAttribute('name'),
                                        'phpType'  => $this->getPhpTypeForSchemaType($attribute->getAttribute('type'), $xmlSchemaPrefix),
                                        'wsdlType' => $attribute->getAttribute('type'),
                                    );
                                }
                                $wsdlType['properties'][] = $property;
                            }
                        }
                    }
                } elseif ($complexContent->length > 0) {
                    $extension = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'extension');
                    if ($extension->length > 0) {
                        $extension = $extension->item(0);
                        $wsdlType['parent'] = $this->getPhpTypeForSchemaType($extension->getAttribute('base'), $xmlSchemaPrefix);
                    }
                    $this->resolveElements($type, $wsdlType, $xmlSchemaPrefix);
                } else {
                    $this->resolveElements($type, $wsdlType, $xmlSchemaPrefix);
                }

                $wsdlTypes[$targetNamespace.'/'.$type->getAttribute('name')] = $wsdlType;
            }

            $elements = $schema->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'element');

            foreach ($elements as $element) {
                list($prefix, $name) = explode(':', $element->getAttribute('type'));

                if ($prefix != $xmlSchemaPrefix && $element->parentNode === $schema) {
                    list($parentXmlPrefix, $parentXmlType) = explode(':', $element->getAttribute('type'));
                    $parentXml = $this->domDocument->lookupNamespaceURI($parentXmlPrefix).'/'.$parentXmlType;
                    $wsdlType = array(
                        'wsdl'      => $this->wsdlFile,
                        'namespace' => $namespace,
                        'name'      => $element->getAttribute('name'),
                        'parent'    => $this->getPhpTypeForSchemaType($element->getAttribute('type'), $xmlSchemaPrefix),
                        'parentXml' => $parentXml,
                        'properties' => array(),
                    );
                    $wsdlTypes[$targetNamespace.'/'.$element->getAttribute('name')] = $wsdlType;
                } elseif ($prefix == $xmlSchemaPrefix) {
                    $wsdlType = array(
                        'wsdl'      => $this->wsdlFile,
                        'namespace' => $namespace,
                        'name'      => $element->getAttribute('name'),
                        'properties' => array(
                            array(
                                'name'     => '_',
                                'phpType'  => $this->getPhpTypeForSchemaType($element->getAttribute('type'), $xmlSchemaPrefix),
                                'wsdlType' => $element->getAttribute('type'),
                            ),
                        ),
                    );
                    $wsdlTypes[$targetNamespace.'/'.$element->getAttribute('name')] = $wsdlType;
                }
            }
        }

        return $wsdlTypes;
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
        $namespace = str_replace(array('http://', '/'), array('','\\'), $ns);
        $namespace = preg_replace('/[^\w\\\]/', '_', $namespace);
        $namespace = rtrim($namespace, '\\');

        return $namespace;
    }

    private function getPhpTypeForSchemaType($xmlSchemaType, $xmlSchemaPrefix)
    {
        // XML schema types
        if ($xmlSchemaPrefix == substr($xmlSchemaType, 0, strlen($xmlSchemaPrefix))) {
            return XmlSchemaMapper::xmlSchemaToPhpType($xmlSchemaType, $xmlSchemaPrefix);
        }

        // PHP type for complex type (=class name)...
        list($prefix, $name) = explode(':', $xmlSchemaType);
        $ns = $this->domDocument->lookupNamespaceURI($prefix);
        $namespace = $this->convertXmlNsToPhpNs($ns);
        return '\\' . $namespace . '\\' . $name;
    }

    private function resolveRestrictions($type, &$property)
    {
        $restriction = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'restriction');

        if ($restriction->length > 0) {
            $enumeration = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'enumeration');
            if ($enumeration->length > 0) {
                $property['enum'] = array();
                foreach ($enumeration as $enum) {
                    $property['enum'][] = $enum->getAttribute('value');
                }
            }

            $minLength = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'minLength');
            if ($minLength->length > 0) {
                $property['minLength'] = $minLength->item(0)->getAttribute('value');
            }

            $maxLength = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'maxLength');
            if ($maxLength->length > 0) {
                $property['maxLength'] = $maxLength->item(0)->getAttribute('value');
            }
        }
    }

    private function resolveAttribute($schema, $attribute, $xmlSchemaPrefix)
    {
        list($prefix, $name) = explode(':', $attribute->getAttribute('ref'));
        $ns = $schema->lookupNamespaceURI($prefix);
        $query = '//xsd:schema[@targetNamespace="'. $ns .'"]/xsd:attribute[@name="'. $name .'"]';
        $attributeRef = $this->domXpath->query($query, $schema);

        if ($attributeRef->length > 0) {
            $attributeElement = $attributeRef->item(0);

            $restriction = $attributeElement->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'restriction')->item(0);

            $property = array(
                'name'     => $attributeElement->getAttribute('name'),
                'phpType'  => $this->getPhpTypeForSchemaType($restriction->getAttribute('base'), $xmlSchemaPrefix),
                'wsdlType' => $restriction->getAttribute('base'),
            );

            $this->resolveRestrictions($attributeElement, $property);

            return $property;
        }
    }

    private function resolveElements($type, &$wsdlType, $xmlSchemaPrefix)
    {
        $elements = $type->getElementsByTagNameNS(Helper::NS_XML_SCHEMA, 'element');

        if ($elements->length > 0) {
            $wsdlType['properties'] = array();
            foreach ($elements as $element) {
                $property = array(
                    'name'     => $element->getAttribute('name'),
                    'phpType'  => $this->getPhpTypeForSchemaType($element->getAttribute('type'), $xmlSchemaPrefix),
                    'wsdlType' => $element->getAttribute('type'),
                );

                if ($element->hasAttribute('maxOccurs') && ($element->getAttribute('maxOccurs') > 1 || 'unbounded' == $element->getAttribute('maxOccurs'))) {
                    $property['phpType'] = 'array(' . $property['phpType'] . ')';
                }

                $this->resolveRestrictions($type, $property);

                $wsdlType['properties'][] = $property;
            }
        }
    }
}