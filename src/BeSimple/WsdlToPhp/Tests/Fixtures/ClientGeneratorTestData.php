<?php

return array(
    'wsdl' => 'http://localhost/',
    'namespace' => 'My\\Webservices',
    'name' => 'Client',
    'documentation' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et',
    'operations' => array(
        array(
            'name' => 'getVersion',
            'documentation' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et',
            'parameters' => array(
            ),
            'return' => 'string',
        ),
        array(
            'name' => 'getCustomer',
            'documentation' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et',
            'parameters' => array(
                'name' => 'string',
                'firstname' => 'string',
                'country' => 'string',
            ),
            'wrapParameters' => 'My\\Webservices\\Customer',
            'return' => 'string',
        ),
        array(
            'name' => 'getProductName',
            'documentation' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et',
            'parameters' => array(
                'id' => 'int',
            ),
            'return' => 'string',
        ),
        array(
            'name' => 'getCarByCustomer',
            'documentation' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et',
            'parameters' => array(
                'client' => 'My\\Webservices\\Customer',
            ),
            'return' => 'My\\Webservices\\Car',
        ),
    ),
    'types' => array(
        'Customer' => 'My\\Webservices\\Customer',
        'OnlineCustomer' => 'My\\Webservices\\OnlineCustomer',
        'Car' => 'My\\Webservices\\Car',
    ),
);
