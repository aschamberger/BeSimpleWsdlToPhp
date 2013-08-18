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
class OnlineCustomer extends Customer
{
    /**
     * email
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: string
     *
     * @var string
     */
    protected $email;

    /**
     * website
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: string
     *
     * @var string
     */
    protected $website;

    /**
     * Constructor.
     *
     * @param string $email
     * @param string $website
     */
    public function __construct($email, $website)
    {
        $this->email = $email;
        $this->website = $website;
    }

    /**
     * @param string $email
     *
     * @return OnlineCustomer
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $website
     *
     * @return OnlineCustomer
     */
    public function setWebsite($website)
    {
        $this->website = $website;
        return $this;
    }

    /**
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }
}
