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
            ),
            array(
                'name' => 'firstname',
                'phpType' => 'string',
                'wsdlType' => 'string',
            ),
            array(
                'name' => 'country',
                'phpType' => 'string',
                'wsdlType' => 'string',
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
                'phpType' => 'string',
                'wsdlType' => 'string',
                'enum' => array(
                    'Audi',
                    'BMW',
                    'Volkswagen',
                ),
                'maxLength' => 20,
            ),
            array(
                'name' => 'model',
                'phpType' => 'string',
                'wsdlType' => 'string',
            ),
            array(
                'name' => 'color',
                'documentation' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et',
                'phpType' => 'string',
                'wsdlType' => 'string',
                'minLength' => 0,
                'maxLength' => 20,
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
            ),
            array(
                'name' => 'website',
                'phpType' => 'string',
                'wsdlType' => 'string',
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
    ),
);
