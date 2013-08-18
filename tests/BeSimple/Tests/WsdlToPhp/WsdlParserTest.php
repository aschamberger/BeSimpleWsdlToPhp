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

namespace BeSimple\Tests\WsdlToPhp;

use BeSimple\WsdlToPhp\WsdlParser;

class WsdlParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getWsdlTypesSimpleAndIncludes()
    {
        $wsdlPath = __DIR__ . '/../Fixtures/wsdl/includes.wsdl';
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
    }

    /**
     * @test
     */
    public function getWsdlTypesComplex()
    {
        $wsdlPath = __DIR__ . '/../Fixtures/wsdl/complex_types.wsdl';
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
    }

    /**
     * @test
     */
    public function getWsdlTypesCascade()
    {
        $wsdlPath = __DIR__ . '/../Fixtures/wsdl/cascade.wsdl';
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
    }

    /**
     * @test
     */
    public function getWsdlTypesRestrictions()
    {
        $wsdlPath = __DIR__ . '/../Fixtures/wsdl/restrictions.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        $this->assertEquals(
            array(
                'char' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => '',
                    'name' => 'char',
                    'properties' => array(
                        array(
                            'restrictions' => array (),
                            'name' => '_',
                            'phpType' => 'int',
                            'wsdlType' => 'xs:int',
                        ),
                    ),
                ),
                'guid' => array(
                    'wsdl' => $wsdlPath,
                    'namespace' => '',
                    'name' => 'guid',
                    'properties' => array(
                        array (
                            'restrictions' => array(
                                'pattern' => '[\\da-fA-F]{8}-[\\da-fA-F]{4}-[\\da-fA-F]{4}-[\\da-fA-F]{4}-[\\da-fA-F]{12}',
                            ),
                            'name' => '_',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
                        ),
                    ),
                ),
            ),
            $parser->getWsdlTypes()
        );
    }

    /**
     * @test
     */
    public function parseWsdlOperations()
    {
        $wsdlPath = __DIR__ . '/../Fixtures/wsdl/operations.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        $this->assertEquals(
            array (
                array (
                    'name' => 'IBillingDataManagementService',
                    'parameters' => array(
                        'AddBlindPaymentRequest' => 'AddBlindPaymentRequest',
                    ),
                    'wrapParameters' => '\\AddBlindPayment',
                    'return' => '\\AddBlindPaymentResponse',
                ),
            ),
            $parser->getWsdlOperations()
        );
    }
}
