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
     * - SchemaType: string
     * - MaxLength: 20
     *
     * @var string
     */
    public $brand;

    /**
     * model
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: string
     *
     * @var string
     */
    public $model;

    /**
     * color
     *
     * Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy
     * eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam
     * voluptua. At vero eos et accusam et
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: string
     * - MinLength: 0
     * - MaxLength: 20
     *
     * @var string
     */
    public $color;
}
