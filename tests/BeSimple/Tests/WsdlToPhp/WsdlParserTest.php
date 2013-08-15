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
                            )
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
                            )
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
                        ),
                        array(
                            'name' => 'Password',
                            'phpType' => 'string',
                            'wsdlType' => 'xs:string',
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
                        )
                    )
                )
            ),
            $parser->getWsdlTypes()
        );
    }

    /**
     * @~test
     */
    public function getWsdlTypes()
    {
        $wsdlPath = __DIR__ . '/../Fixtures/wsdl/error.wsdl';
        $parser = new WsdlParser($wsdlPath, SOAP_1_2);

        print_r($parser->getWsdlTypes());exit;
        $this->assertEquals(
            array(
            ),
            $parser->getWsdlTypes()
        );
    }
}
