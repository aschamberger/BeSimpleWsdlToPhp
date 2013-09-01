<?php

/*
 * This file is part of BeSimpleWsdlToPhp.
 *
 * (c) Christian Kerl <christian-kerl@web.de>
 * (c) Francis Besset <francis.besset@gmail.com>
 * (c) Andreas Schamberger <mail@andreass.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace BeSimple\WsdlToPhp;

/**
 * XML Schema type constants and php type mapping.
 * http://www.w3.org/TR/xmlschema-2/#d0e11239
 *
 * @author Andreas Schamberger <mail@andreass.net>
 */
class XmlSchemaMapper
{
    const XML_SCHEMA_STRING             = 'string';
    const XML_SCHEMA_BOOLEAN            = 'boolean';
    const XML_SCHEMA_FLOAT              = 'float';
    const XML_SCHEMA_DOUBLE             = 'double';
    const XML_SCHEMA_DECIMAL            = 'decimal';
    const XML_SCHEMA_DURATION           = 'duration';
    const XML_SCHEMA_DATETIME           = 'dateTime';
    const XML_SCHEMA_TIME               = 'time';
    const XML_SCHEMA_DATE               = 'date';
    const XML_SCHEMA_GYEARMONTH         = 'gYearMonth';
    const XML_SCHEMA_GYEAR              = 'gYear';
    const XML_SCHEMA_GMONTHDAY          = 'gMonthDay';
    const XML_SCHEMA_GDAY               = 'gDay';
    const XML_SCHEMA_GMONTH             = 'gMonth';
    const XML_SCHEMA_HEXBINARY          = 'hexBinary';
    const XML_SCHEMA_BASE64BINARY       = 'base64Binary';
    const XML_SCHEMA_ANYURI             = 'anyURI';
    const XML_SCHEMA_QNAME              = 'QName';
    const XML_SCHEMA_NOTATION           = 'NOTATION';
    const XML_SCHEMA_NORMALIZEDSTRING   = 'normalizedString';
    const XML_SCHEMA_TOKEN              = 'token';
    const XML_SCHEMA_LANGUAGE           = 'language';
    const XML_SCHEMA_IDREFS             = 'IDREFS';
    const XML_SCHEMA_ENTITIES           = 'ENTITIES';
    const XML_SCHEMA_NMTOKEN            = 'NMTOKEN';
    const XML_SCHEMA_NMTOKENS           = 'NMTOKENS';
    const XML_SCHEMA_NAME               = 'Name';
    const XML_SCHEMA_NCNAME             = 'NCName';
    const XML_SCHEMA_ID                 = 'ID';
    const XML_SCHEMA_IDREF              = 'IDREF';
    const XML_SCHEMA_ENTITY             = 'ENTITY';
    const XML_SCHEMA_INTEGER            = 'integer';
    const XML_SCHEMA_NONPOSITIVEINTEGER = 'nonPositiveInteger';
    const XML_SCHEMA_NEGATIVEINTEGER    = 'negativeInteger';
    const XML_SCHEMA_LONG               = 'long';
    const XML_SCHEMA_INT                = 'int';
    const XML_SCHEMA_SHORT              = 'short';
    const XML_SCHEMA_BYTE               = 'byte';
    const XML_SCHEMA_NONNEGATIVEINTEGER = 'nonNegativeInteger';
    const XML_SCHEMA_UNSIGNEDLONG       = 'unsignedLong';
    const XML_SCHEMA_UNSIGNEDINT        = 'unsignedInt';
    const XML_SCHEMA_UNSIGNEDSHORT      = 'unsignedShort';
    const XML_SCHEMA_UNSIGNEDBYTE       = 'unsignedByte';
    const XML_SCHEMA_POSITIVEINTEGER    = 'positiveInteger';

    private static $xmlSchemaToPhpType = array(
        'string'             => 'string',
        'boolean'            => 'boolean',
        'float'              => 'float',
        'double'             => 'float',
        'decimal'            => 'float',
        'duration'           => 'string',
        'dateTime'           => 'string',
        'time'               => 'string',
        'date'               => 'string',
        'gYearMonth'         => 'string',
        'gYear'              => 'string',
        'gMonthDay'          => 'string',
        'gDay'               => 'string',
        'gMonth'             => 'string',
        'hexBinary'          => 'string',
        'base64Binary'       => 'string',
        'anyURI'             => 'string',
        'QName'              => 'string',
        'NOTATION'           => 'string',
        'normalizedString'   => 'string',
        'token'              => 'string',
        'language'           => 'string',
        'IDREFS'             => 'string',
        'ENTITIES'           => 'string',
        'NMTOKEN'            => 'string',
        'NMTOKENS'           => 'string',
        'Name'               => 'string',
        'NCName'             => 'string',
        'ID'                 => 'string',
        'IDREF'              => 'string',
        'ENTITY'             => 'string',
        'integer'            => 'int',
        'nonPositiveInteger' => 'int',
        'negativeInteger'    => 'int',
        'long'               => 'int',
        'int'                => 'int',
        'short'              => 'int',
        'byte'               => 'int',
        'nonNegativeInteger' => 'int',
        'unsignedLong'       => 'int',
        'unsignedInt'        => 'int',
        'unsignedShort'      => 'int',
        'unsignedByte'       => 'int',
        'positiveInteger'    => 'int',
    );

    /**
     * Converts XML Schema type to PHP type.
     *
     * @param string $xmlSchemaType XML Schema type to convert
     * @param string $prefix        Namespace prefix
     *
     * @return string
     */
    public static function xmlSchemaToPhpType($xmlSchemaType, $prefix='xsd')
    {
        $xmlSchemaType = substr($xmlSchemaType, strlen($prefix)+1);

        return isset(self::$xmlSchemaToPhpType[$xmlSchemaType])?
            self::$xmlSchemaToPhpType[$xmlSchemaType]:
            $xmlSchemaType;
    }

    /**
     * Get all XML schema types.
     *
     * @return array
     */
    public static function getAllTypes()
    {
        return self::$xmlSchemaToPhpType;
    }

}
