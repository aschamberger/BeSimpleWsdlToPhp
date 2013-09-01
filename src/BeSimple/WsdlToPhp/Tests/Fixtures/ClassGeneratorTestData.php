<?php

return array(
    'customer' => array(
        'wsdl' => 'http://localhost/',
        'namespace' => 'My\\Webservices',
        'name' => 'Customer',
        'documentation' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et',
        'properties' => array(
            array(
                'name' => 'name',
                'phpType' => 'string',
                'wsdlType' => 'string',
                'restrictions' => array(),
                'isNull' => false,
            ),
            array(
                'name' => 'firstname',
                'phpType' => 'string',
                'wsdlType' => 'string',
                'restrictions' => array(),
                'isNull' => false,
            ),
            array(
                'name' => 'country',
                'phpType' => 'string',
                'wsdlType' => 'string',
                'restrictions' => array(),
                'isNull' => false,
            ),
        ),
    ),
    'car' => array(
        'wsdl' => 'http://localhost/',
        'namespace' => 'My\\Webservices',
        'name' => 'Car',
        'documentation' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et',
        'properties' => array(
            array(
                'name' => 'brand',
                'phpType' => 'Brand',
                'wsdlType' => 'Brand',
                'enum' => array(
                    'Audi',
                    'BMW',
                    'Volkswagen',
                ),
                'restrictions' => array(
                    'maxLength' => 20
                ),
                'isNull' => true,
            ),
            array(
                'name' => 'model',
                'phpType' => 'string',
                'wsdlType' => 'string',
                'restrictions' => array(),
                'isNull' => false,
            ),
            array(
                'name' => 'color',
                'documentation' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et',
                'phpType' => 'string',
                'wsdlType' => 'string',
                'restrictions' => array(
                    'minLength' => 0,
                    'maxLength' => 20,
                ),
                'isNull' => false,
            ),
        ),
    ),
    'online_customer' => array(
        'wsdl' => 'http://localhost/',
        'namespace' => 'My\\Webservices',
        'name' => 'OnlineCustomer',
        'parent' => 'Customer',
        'documentation' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et',
        'properties' => array(
            array(
                'name' => 'email',
                'phpType' => 'string',
                'wsdlType' => 'string',
                'restrictions' => array(),
                'isNull' => false,
            ),
            array(
                'name' => 'website',
                'phpType' => 'string',
                'wsdlType' => 'string',
                'restrictions' => array(),
                'isNull' => false,
            ),
        ),
    ),
    'empty' => array(
        'wsdl' => 'http://localhost/',
        'namespace' => '',
        'name' => 'EmptyClass',
        'parent' => '',
        'documentation' => '',
        'properties' => array(),
        'isNull' => false,
    ),
);
