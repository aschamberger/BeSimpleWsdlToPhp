<?php

namespace My\Webservices;

use \SoapClient as BaseSoapClient;

/**
 * This class is generated from the following WSDL:
 * http://localhost/
 *
 * Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
 * eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam
 * voluptua. At vero eos et accusam et
 */
class Client extends BaseSoapClient
{
    protected $classMap = array(
        'Customer' => 'My\\Webservices\\Customer',
        'OnlineCustomer' => 'My\\Webservices\\OnlineCustomer',
        'Car' => 'My\\Webservices\\Car',
    );

    /**
     * Constructor.
     *
     * @param string               $wsdl    WSDL file
     * @param array(string=>mixed) $options Options array
     */
    public function __construct($wsdl, array $options = array())
    {
        if (!isset($options['classmap'])) {
            $options['classmap'] = $this->getClassMap();
        }

        parent::__construct($wsdl, $options);
    }

    public function getClassMap()
    {
        return $this->classMap;
    }

    /**
     * getVersion
     *
     * Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
     * eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam
     * voluptua. At vero eos et accusam et
     *
     * @return string
     */
    public function getVersion()
    {
        $parameters = func_get_args();

        return $this->__soapCall('getVersion', array('parameters' => $parameters));
    }

    /**
     * getCustomer
     *
     * Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
     * eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam
     * voluptua. At vero eos et accusam et
     *
     * @param string $name
     * @param string $firstname
     * @param string $country
     *
     * @return string
     */
    public function getCustomer($name, $firstname, $country)
    {
        $parameters = new My\Webservices\Customer();
        $parameters->name = $name;
        $parameters->firstname = $firstname;
        $parameters->country = $country;

        return $this->__soapCall('getCustomer', array('parameters' => $parameters));
    }

    /**
     * getProductName
     *
     * Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
     * eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam
     * voluptua. At vero eos et accusam et
     *
     * @param int $id
     *
     * @return string
     */
    public function getProductName($id)
    {
        $parameters = func_get_args();

        return $this->__soapCall('getProductName', array('parameters' => $parameters));
    }

    /**
     * getCarByCustomer
     *
     * Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
     * eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam
     * voluptua. At vero eos et accusam et
     *
     * @param My\Webservices\Customer $client
     *
     * @return My\Webservices\Car
     */
    public function getCarByCustomer(My\Webservices\Customer $client)
    {
        $parameters = func_get_args();

        return $this->__soapCall('getCarByCustomer', array('parameters' => $parameters));
    }
}
