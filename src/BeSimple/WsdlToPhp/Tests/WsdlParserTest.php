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

namespace BeSimple\WsdlToPhp\Tests;

use BeSimple\WsdlToPhp\WsdlParserError;

use BeSimple\WsdlToPhp\WsdlParser;

class WsdlParserTest extends \PHPUnit_Framework_TestCase
{
    protected $fixturesDir;

    protected function setUp()
    {
        $this->fixturesDir = __DIR__ . "/Fixtures";

        $wsdlDir = $this->fixturesDir.'/wsdl/';

        libxml_set_external_entity_loader(
            function ($public, $system, $context) use($wsdlDir) {
                if (is_file($system)) {
                    return $system;
                }
                return $wsdlDir.basename($system);
            }
        );
    }

    /**
     * @test
     *
     * @see: http://www.w3schools.com/schema/schema_complex_indicators.asp
     */
    public function getWsdlTypesComplexTypeIndicatorsNestedTypes()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/complexTypeIndicatorsNestedTypes.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);
        $parser->getWsdlTypes();

        $this->assertTrue($parser->hasErrors());

        $message = "Nested complexType element declaration in XML schema for type 'persons' not supported.";
        $line = 9;
        $error = new WsdlParserError($message, $wsdlPath, $line);
        $errors = $parser->getErrors();

        $this->assertEquals($error, $errors[0]);
    }


    /**
     * @test
     *
     * @see: http://www.w3schools.com/schema/schema_complex_indicators.asp
     */
    public function getWsdlTypesComplexTypeIndicators()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/complexTypeIndicators.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        $this->assertEquals(
            array(
                'http://wsdl.besim.pl/WsdlToPhp/personAll' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'personAll',
                    'properties' => array(
                        array (
                            'name' => 'firstname',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'lastname',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/personChoice' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'personChoice',
                    'properties' => array(
                        array (
                            'name' => 'employee',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'member',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/personSequence' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'personSequence',
                    'properties' => array(
                        array (
                            'name' => 'firstname',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'lastname',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/personMax' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'personMax',
                    'properties' => array(
                        array (
                            'name' => 'full_name',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'child_name',
                            'phpType' => 'array(string)',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/personMin' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'personMin',
                    'properties' => array(
                        array (
                            'name' => 'full_name',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'child_name',
                            'phpType' => 'array(string)',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
            ),
            $parser->getWsdlTypes()
        );
        $this->assertFalse($parser->hasErrors());
    }

    /**
     * @test
     *
     * @see: http://www.w3.org/TR/2001/REC-xmlschema-0-20010502/#address.xsd
     */
    public function getWsdlTypesComplexTypeComplexContent()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/complexTypeComplexContent.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        $this->assertEquals(
            array(
                'http://wsdl.besim.pl/WsdlToPhp/Address' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'Address',
                    'properties' => array(
                        array (
                            'name' => 'name',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'street',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'city',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/USAddress' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'USAddress',
                    'parent' => 'pl\besim\wsdl\WsdlToPhp\Address',
                    'properties' => array(
                        array (
                            'name' => 'state',
                            'phpType' => 'pl\besim\wsdl\WsdlToPhp\USState',
                            'wsdlType' => 'tns:USState',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'zip',
                            'phpType' => 'int',
                            'wsdlType' => 'xs:positiveInteger',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/DEAddress' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'DEAddress',
                    'parent' => 'pl\besim\wsdl\WsdlToPhp\Address',
                    'properties' => array(
                        array (
                            'name' => 'postcode',
                            'phpType' => 'int',
                            'wsdlType' => 'xs:positiveInteger',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/USState' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'USState',
                    'properties' => array(
                        array (
                            'name' => '_',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'enum' => array(
                                'AK',
                                'AL',
                                'AR',
                            ),
                            'isNull' => false,
                        ),
                    ),
                ),
            ),
            $parser->getWsdlTypes()
        );
        $this->assertFalse($parser->hasErrors());
    }

    /**
     * @test
     *
     * @see: http://www.w3schools.com/schema/schema_complex_text.asp
     */
    public function getWsdlTypesComplexTypeSimpleContent()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/complexTypeSimpleContent.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        $this->assertEquals(
            array(
                'http://wsdl.besim.pl/WsdlToPhp/shoesizeInline' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'shoesizeInline',
                    'properties' => array(
                        array (
                            'name' => '_',
                            'phpType' => 'int',
                            'wsdlType' => 'xs:integer',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'country',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/shoesizeReference' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'shoesizeReference',
                    'parent' => 'pl\besim\wsdl\WsdlToPhp\shoetype',
                    'properties' => array(),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/shoetype' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'shoetype',
                    'properties' => array(
                        array (
                            'name' => '_',
                            'phpType' => 'int',
                            'wsdlType' => 'xs:integer',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'country',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
            ),
            $parser->getWsdlTypes()
        );
        $this->assertFalse($parser->hasErrors());
    }

    /**
     * @test
     *
     * Same schema as in complexType test but spread over external and internal import
     */
    public function getWsdlTypesSchemaIncludes()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/schemaIncludes.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        $this->assertEquals(
            array(
                'http://wsdl.besim.pl/WsdlToPhp/person' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'person',
                    'properties' => array(
                        array (
                            'name' => 'firstname',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'lastname',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/employee' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'employee',
                    'parent' => 'pl\besim\wsdl\WsdlToPhp3\fullpersoninfo',
                    'properties' => array(),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/student' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'student',
                    'parent' => 'pl\besim\wsdl\WsdlToPhp2\personinfo',
                    'properties' => array(),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/member' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'member',
                    'parent' => 'pl\besim\wsdl\WsdlToPhp2\personinfo',
                    'properties' => array(),
                ),
                'http://wsdl.besim.pl/WsdlToPhp2/personinfo' => array(
                    'wsdl' => 'schemaInclude.xsd',
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp2',
                    'name' => 'personinfo',
                    'properties' => array(
                        array (
                            'name' => 'firstname',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'lastname',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp3/fullpersoninfo' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp3',
                    'name' => 'fullpersoninfo',
                    'parent' => 'pl\besim\wsdl\WsdlToPhp2\personinfo',
                    'properties' => array(
                        array (
                            'name' => 'address',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'city',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'country',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
            ),
            $parser->getWsdlTypes()
        );
        $this->assertFalse($parser->hasErrors());
    }

    /**
     * @test
     *
     * @see: http://www.w3schools.com/schema/schema_complex.asp
     */
    public function getWsdlTypesComplexTypes()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/complexTypes.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        $this->assertEquals(
            array(
                'http://wsdl.besim.pl/WsdlToPhp/person' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'person',
                    'properties' => array(
                        array (
                            'name' => 'firstname',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'lastname',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/employee' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'employee',
                    'parent' => 'pl\besim\wsdl\WsdlToPhp\fullpersoninfo',
                    'properties' => array(),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/student' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'student',
                    'parent' => 'pl\besim\wsdl\WsdlToPhp\personinfo',
                    'properties' => array(),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/member' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'member',
                    'parent' => 'pl\besim\wsdl\WsdlToPhp\personinfo',
                    'properties' => array(),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/personinfo' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'personinfo',
                    'properties' => array(
                        array (
                            'name' => 'firstname',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'lastname',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/fullpersoninfo' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'fullpersoninfo',
                    'parent' => 'pl\besim\wsdl\WsdlToPhp\personinfo',
                    'properties' => array(
                        array (
                            'name' => 'address',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'city',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                        array (
                            'name' => 'country',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'isNull' => false,
                        ),
                    ),
                ),
            ),
            $parser->getWsdlTypes()
        );
        $this->assertFalse($parser->hasErrors());
    }

    /**
     * @test
     *
     * @see: http://www.w3schools.com/schema/schema_facets.asp
     */
    public function getWsdlTypesRestrictions()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/restrictions.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        $this->assertEquals(
            array(
                'http://wsdl.besim.pl/WsdlToPhp/age' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'age',
                    'properties' => array(
                        array(
                            'name' => '_',
                            'phpType' => 'int',
                            'wsdlType' => 'xs:integer',
                            'restrictions' => array (
                                'minInclusive' => 0,
                                'maxInclusive' => 120,
                            ),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/car' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'car',
                    'properties' => array(
                        array (
                            'name' => '_',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(),
                            'enum' => array(
                                'Audi',
                                'Golf',
                                'BMW',
                            ),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/letter' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'letter',
                    'properties' => array(
                        array (
                            'name' => '_',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(
                                'pattern' => '[a-z]',
                            ),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/prodid' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'prodid',
                    'properties' => array(
                        array (
                            'name' => '_',
                            'phpType' => 'int',
                            'wsdlType' => 'xs:integer',
                            'restrictions' => array(
                                'pattern' => '[0-9][0-9][0-9][0-9][0-9]',
                            ),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/password1' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'password1',
                    'properties' => array(
                        array (
                            'name' => '_',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(
                                'length' => 8,
                            ),
                            'isNull' => false,
                        ),
                    ),
                ),
                'http://wsdl.besim.pl/WsdlToPhp/password2' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => 'pl\besim\wsdl\WsdlToPhp',
                    'name' => 'password2',
                    'properties' => array(
                        array (
                            'name' => '_',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(
                                'minLength' => 5,
                                'maxLength' => 8,
                            ),
                            'isNull' => false,
                        ),
                    ),
                ),
            ),
            $parser->getWsdlTypes()
        );
        $this->assertFalse($parser->hasErrors());
    }

    /**
     * @test
     */
    public function getWsdlOperations()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/operations.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        $this->assertEquals(
            array(
                array(
                    'name' => 'TestOperationOne',
                    'parameters' => array(
                        'TestOperationOneRequest' => 'string',
                    ),
                    'wrapParameters' => 'pl\besim\wsdl\WsdlToPhp\TestOperationOne',
                    'return' => 'pl\besim\wsdl\WsdlToPhp\TestOperationOneResponse',
                ),
                array(
                    'name' => 'TestOperationTwo',
                    'parameters' => array(
                        'TestOperationTwoRequest' => 'string'
                    ),
                    'wrapParameters' => 'pl\besim\wsdl\WsdlToPhp\TestOperationTwo',
                    'return' => 'pl\besim\wsdl\WsdlToPhp\TestOperationTwoResponse',
                )
            ),
            $parser->getWsdlOperations()
        );
        $this->assertFalse($parser->hasErrors());
    }

    /**
     * @test
     */
    public function getWsdlOperationsNoWsdl2javaStyleNamespaces()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/operations.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2, array('wsdl2java_style' => false));

        $this->assertEquals(
            array(
                array(
                    'name' => 'TestOperationOne',
                    'parameters' => array(
                        'TestOperationOneRequest' => 'string',
                    ),
                    'wrapParameters' => 'TestOperationOne',
                    'return' => 'TestOperationOneResponse',
                ),
                array(
                    'name' => 'TestOperationTwo',
                    'parameters' => array(
                        'TestOperationTwoRequest' => 'string'
                    ),
                    'wrapParameters' => 'TestOperationTwo',
                    'return' => 'TestOperationTwoResponse',
                )
            ),
            $parser->getWsdlOperations()
        );
        $this->assertFalse($parser->hasErrors());
    }

    /**
     * @test
     */
    public function getWsdlEnums()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/enum.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2, array('wsdl2java_style' => false));

        $this->assertEquals(
            array(
                'http://wsdl.besim.pl/WsdlToPhp/AccountType' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => '',
                    'name' => 'AccountType',
                    'properties' => array(
                        array(
                            'name' => '_',
                            'restrictions' => array(),
                            'enum' => array(
                                'Unassigned',
                                'Personal',
                                'Business'
                            ),
                            'wsdlType' => 'xs:string',
                            'phpType' => 'string',
                            'isNull' => false
                        )
                    )
                )
            ),
            $parser->getWsdlTypes()
        );
        $this->assertFalse($parser->hasErrors());
    }
}
