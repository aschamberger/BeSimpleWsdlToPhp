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
class Car
{
    const BRAND_AUDI = 'Audi';
    const BRAND_BMW = 'BMW';
    const BRAND_VOLKSWAGEN = 'Volkswagen';

    /**
     * brand
     *
     * The property can have one of the following values:
     * - self::BRAND_AUDI (Audi)
     * - self::BRAND_BMW (BMW)
     * - self::BRAND_VOLKSWAGEN (Volkswagen)
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: Brand
     * - maxLength: 20
     *
     * @var string
     */
    protected $brand = null;

    /**
     * model
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: string
     *
     * @var string
     */
    protected $model;

    /**
     * color
     *
     * Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
     * eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam
     * voluptua. At vero eos et accusam et
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: string
     * - minLength: 0
     * - maxLength: 20
     *
     * @var string
     */
    protected $color;

    /**
     * Constructor.
     *
     * @param string $model
     * @param string $color
     * @param string $brand
     */
    public function __construct($model, $color, $brand = null)
    {
        $this->model = $model;
        $this->color = $color;
        $this->brand = $brand;
    }

    /**
     * @param string $brand
     *
     * @return Car
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return string
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param string $model
     *
     * @return Car
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param string $color
     *
     * @return Car
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }
}
