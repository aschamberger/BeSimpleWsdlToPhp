<?php

namespace My\Webservices;

/**
 * This class is generated from the following WSDL:
 * http://localhost/
 *
 * Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
 * eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam
 * voluptua. At vero eos et accusam et
 */
class Customer
{
    /**
     * name
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: string
     *
     * @var string
     */
    protected $name;

    /**
     * firstname
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: string
     *
     * @var string
     */
    protected $firstname;

    /**
     * country
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: string
     *
     * @var string
     */
    protected $country;

    /**
     * Constructor.
     *
     * @param string $name
     * @param string $firstname
     * @param string $country
     */
    public function __construct($name, $firstname, $country)
    {
        $this->name = $name;
        $this->firstname = $firstname;
        $this->country = $country;
    }

    /**
     * @param string $name
     *
     * @return Customer
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $firstname
     *
     * @return Customer
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $country
     *
     * @return Customer
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }
}
