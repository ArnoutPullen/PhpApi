<?php

namespace OData;


/**
 * Class Char
 * @package POData\Providers\Metadata\Type
 */
class Char
{
    const A = 65;
    const Z = 90;
    const SMALL_A = 97;
    const SMALL_Z = 122;
    const F = 70;
    const SMALL_F = 102;
    const ZERO = 48;
    const NINE = 57;
    const TAB = 9;
    const NEWLINE = 10;
    const CARRIAGE_RETURN = 13;
    const SPACE = 32;

    /**
     * Gets full name of this type in EDM namespace
     * Note: implementation of IType::getFullTypeName
     *
     * @return string
     */
    public function getFullTypeName()
    {
        return 'System.Char';
    }

    /**
     * Converts the given string value to char type.
     * Note: This function will not perform any conversion.
     *
     * @param string $stringValue The value to convert.
     *
     * @return string
     */
    public function convert($stringValue)
    {
        return $stringValue;
    }

    /**
     * Convert the given value to a form that can be used in OData uri.
     * Note: The calling function should not pass null value, as this
     * function will not perform any check for nullability
     *
     * @param mixed $value The value to convert.
     *
     * @return string
     */
    public function convertToOData($value)
    {
        return $value;
    }

    /**
     * Checks a character is whilespace
     *
     * @param char $char character to check
     *
     * @return boolean
     */
    public static function isWhiteSpace($char)
    {
        $asciiVal = ord($char);
        return $asciiVal == Char::SPACE
            || $asciiVal == Char::TAB
            || $asciiVal == Char::CARRIAGE_RETURN
            || $asciiVal == Char::NEWLINE;
    }

    /**
     * Checks a character is letter
     *
     * @param char $char character to check
     *
     * @return boolean
     */
    public static function isLetter($char)
    {
        $asciiVal = ord($char);
        return ($asciiVal >= Char::A && $asciiVal <= Char::Z)
            || ($asciiVal >= Char::SMALL_A && $asciiVal <= Char::SMALL_Z);
    }

    /**
     * Checks a character is digit
     *
     * @param char $char character to check
     *
     * @return boolean
     */
    public static function isDigit($char)
    {
        $asciiVal = ord($char);
        return $asciiVal >= Char::ZERO
            && $asciiVal <= Char::NINE;
    }


    /**
     * Checks a character is letter or digit
     *
     * @param char $char character to check
     *
     * @return boolean
     */
    public static function isLetterOrDigit($char)
    {
        return self::isDigit($char) || self::isLetter($char);
    }
}