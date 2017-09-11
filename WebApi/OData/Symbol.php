<?php

namespace OData;
/**
 * Class ExpressionTokenId
 */
class Symbol
{
    const UNKNOWN = 1;
    const END = 2;
    const EQUAL = 3;
    const IDENTIFIER = 4;
    const NULL_LITERAL = 5;
    const BOOLEAN_LITERAL = 6;
    const STRING_LITERAL = 7;
    const INTEGER_LITERAL = 8;
    const INT64_LITERAL = 9;
    const SINGLE_LITERAL = 10;
    const DATETIME_LITERAL = 11;
    const DECIMAL_LITERAL = 12;
    const DOUBLE_LITERAL = 13;
    const GUID_LITERAL = 14;
    const BINARY_LITERAL = 15;
    const EXCLAMATION = 16;
    const OPENPARAM = 17;
    const CLOSEPARAM = 18;
    const COMMA = 19;
    const MINUS = 20;
    const SLASH = 21;
    const QUESTION = 22;
    const DOT = 23;
    const STAR = 24;
    const COLON = 25;
}