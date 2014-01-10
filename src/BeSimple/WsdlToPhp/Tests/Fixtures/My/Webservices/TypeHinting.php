<?php

/**
 * This class is generated from the following WSDL:
 * http://localhost/
 */
class TypeHinting
{
    /**
     * brand
     *
     * The property can have one of the following values:
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: xs:brand
     *
     * @var Brand
     */
    protected $brand;

    /**
     * enum
     *
     * The property can have one of the following values:
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: xs:enum
     *
     * @var Enum
     */
    protected $enum;

    /**
     * arr
     *
     * The property can have one of the following values:
     *
     * The property has the following characteristics/restrictions:
     * - SchemaType: xs:array
     *
     * @var array
     */
    protected $arr = null;

    /**
     * Constructor.
     *
     * @param Brand $brand
     * @param Enum  $enum
     * @param array $arr
     */
    public function __construct(Brand $brand, $enum, array $arr = null)
    {
        $this->brand = $brand;
        $this->enum = $enum;
        $this->arr = $arr;
    }

    /**
     * @param Brand $brand
     *
     * @return TypeHinting
     */
    public function setBrand(Brand $brand)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        if (null === $this->brand) {
            $this->brand = new Brand();
        }

        return $this->brand;
    }

    /**
     * @param Enum $enum
     *
     * @return TypeHinting
     */
    public function setEnum($enum)
    {
        $this->enum = $enum;

        return $this;
    }

    /**
     * @return Enum
     */
    public function getEnum()
    {
        return $this->enum;
    }

    /**
     * @param array $arr
     *
     * @return TypeHinting
     */
    public function setArr(array $arr)
    {
        $this->arr = $arr;

        return $this;
    }

    /**
     * @return array
     */
    public function getArr()
    {
        return $this->arr;
    }
}
