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

use BeSimple\WsdlToPhp\WsdlParser;

class WsdlParserTest extends \PHPUnit_Framework_TestCase
{
    protected $fixturesDir;

    protected function setUp()
    {
        $this->fixturesDir = __DIR__ . "/Fixtures";
    }

    /**
     * @test
     */
    public function getWsdlTypesSimpleAndIncludes()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/includes.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        $this->assertEquals(
            array(
                'ACHAccountType' => array(
                    'wsdl' => 'https://heartlandpaymentservices.net/BillingDataManagement/v3/BillingDataManagementService.svc?xsd=xsd3',
                    'namespace' => '',
                    'name' => 'ACHAccountType',
                    'properties' => array(
                        array(
                            'enum' => array(
                                'Unassigned',
                                'Personal',
                                'Business',
                            ),
                            'restrictions' => array(
                                'enumeration' => 'Business'
                            ),
                            'wsdlType' => 'xs:string',
                        )
                    )
                ),
                'ACHDepositType' => array(
                    'wsdl' => 'https://heartlandpaymentservices.net/BillingDataManagement/v3/BillingDataManagementService.svc?xsd=xsd3',
                    'namespace' => '',
                    'name' => 'ACHDepositType',
                    'properties' => array(
                        array(
                            'enum' => array(
                                'Unassigned',
                                'Checking',
                                'Savings',
                            ),
                            'restrictions' => array(
                                'enumeration' => 'Savings'
                            ),
                            'wsdlType' => 'xs:string',
                        )
                    )
                ),
            ),
            $parser->getWsdlTypes()
        );
        $this->assertFalse($parser->hasErrors());
    }

    /**
     * @test
     */
    public function getWsdlTypesComplex()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/complex_types.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        $this->assertEquals(
            array(
                'Credentials' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => '',
                    'name' => 'Credentials',
                    'properties' => array(
                        array(
                            'name' => 'ApplicationID',
                            'phpType' => 'int',
                            'wsdlType' => 'xs:int',
                            'isNull' => true,
                            'restrictions' => array(),
                        ),
                        array(
                            'name' => 'Password',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'isNull' => true,
                            'restrictions' => array(),
                        )
                    )
                )
            ),
            $parser->getWsdlTypes()
        );
        $this->assertFalse($parser->hasErrors());
    }

    /**
     * @test
     */
    public function getWsdlTypesCascade()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/cascade.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        $this->assertEquals(
            array(
                'AddBlindPayment' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => '',
                    'name' => 'AddBlindPayment',
                    'properties' => array(
                        array(
                            'name' => 'AddBlindPaymentRequest',
                            'phpType' => 'AddBlindPaymentRequest',
                            'wsdlType' => 'q1:AddBlindPaymentRequest',
                            'isNull' => true,
                            'restrictions' => array(),
                        )
                    )
                )
            ),
            $parser->getWsdlTypes()
        );
        $this->assertFalse($parser->hasErrors());
    }

    /**
     * @test
     */
    public function getWsdlTypesRestrictions()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/restrictions.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        $this->assertEquals(
            array(
                'char' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => '',
                    'name' => 'char',
                    'properties' => array(
                        array(
                            'name' => '_',
                            'phpType' => 'int',
                            'wsdlType' => 'xs:int',
                            'restrictions' => array (),
                            'isNull' => false
                        ),
                    ),
                ),
                'guid' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => '',
                    'name' => 'guid',
                    'properties' => array(
                        array (
                            'name' => '_',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                            'restrictions' => array(
                                'pattern' => '[\\da-fA-F]{8}-[\\da-fA-F]{4}-[\\da-fA-F]{4}-[\\da-fA-F]{4}-[\\da-fA-F]{12}',
                            ),
                            'isNull' => false
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
     * Fix: parse elements as Type and as Property and creates few Types
     */
    public function getWsdlTypesResolveElements()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/elements.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);
        $parser->getWsdlTypes();
        $this->assertFalse($parser->hasErrors()/*, $parser->getErrors()[0]*/);
    }

    /**
     * @test
     */
    public function parseWsdlOperations()
    {
        $wsdlPath = $this->fixturesDir.'/wsdl/operations.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        $this->assertEquals(
            array(
                array(
                    'name' => 'AddBlindPayment',
                    'parameters' => array(
                        'AddBlindPaymentRequest' => 'AddBlindPaymentRequest',
                    ),
                    'wrapParameters' => 'AddBlindPayment',
                    'return' => 'AddBlindPaymentResponse',
                ),
                array(
                    'name' => 'DisburseFunds',
                    'parameters' => array(
                        'DisburseFundsRequest' => 'DisburseFundsRequest'
                    ),
                    'wrapParameters' => 'DisburseFunds',
                    'return' => 'DisburseFundsResponse',
                )
            ),
            $parser->getWsdlOperations()
        );
        $this->assertFalse($parser->hasErrors());
    }
}
