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

        return parent::__construct($wsdl, $options);
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
        $arguments = func_get_args();

        return $this->__soapCall('getVersion', $arguments);
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
        $arguments = new My\Webservices\Customer();
        $arguments->name = $name;
        $arguments->firstname = $firstname;
        $arguments->country = $country;

        return $this->__soapCall('getCustomer', $arguments);
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
        $arguments = func_get_args();

        return $this->__soapCall('getProductName', $arguments);
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
        $arguments = func_get_args();

        return $this->__soapCall('getCarByCustomer', $arguments);
    }
}
