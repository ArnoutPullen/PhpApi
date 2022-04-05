<?php

namespace OData;

/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 20-7-2017
 * Time: 22:20
 */
class Token
{
    /**
     * @var int
     */
    public $Id;

    /**
     * @var string
     */
    public $Text;

    /**
     * @var int
     */
    public $Position;

    /**
     * Checks whether this token is a comparison operator.
     *
     * @return boolean True if this token represent a comparison operator
     *                 False otherwise.
     */
    public function isComparisonOperator()
    {
        return
            $this->Id == Symbol::IDENTIFIER &&
            (strcmp($this->Text, KeyWord::EQUAL) == 0 ||
                strcmp($this->Text, KeyWord::NOT_EQUAL) == 0 ||
                strcmp($this->Text, KeyWord::LESSTHAN) == 0 ||
                strcmp($this->Text, KeyWord::GREATERTHAN) == 0 ||
                strcmp($this->Text, KeyWord::LESSTHAN_OR_EQUAL) == 0 ||
                strcmp($this->Text, KeyWord::GREATERTHAN_OR_EQUAL) == 0);
    }

    /**
     * Checks whether this token is an equality operator.
     *
     * @return boolean True if this token represent a equality operator
     *                 False otherwise.
     */
    public function isEqualityOperator()
    {
        return
            $this->Id == Symbol::IDENTIFIER &&
            (strcmp($this->Text, KeyWord::EQUAL) == 0 ||
                strcmp($this->Text, KeyWord::NOT_EQUAL) == 0);
    }

    /**
     * Checks whether this token is a valid token for a key value.
     *
     * @return boolean True if this token represent valid key value
     *                 False otherwise.
     */
    public function isKeyValueToken()
    {
        return
            $this->Id == Symbol::BINARY_LITERAL ||
            $this->Id == Symbol::BOOLEAN_LITERAL ||
            $this->Id == Symbol::DATETIME_LITERAL ||
            $this->Id == Symbol::GUID_LITERAL ||
            $this->Id == Symbol::STRING_LITERAL ||
            ExpressionLexer::isNumeric($this->Id);
    }

    /**
     * Gets the current identifier text
     * @return string
     * @throws /Exception
     */
    public function getIdentifier()
    {
        if ($this->Id != Symbol::IDENTIFIER) {
            throw new \Exception(
                'Identifier expected at position ' . $this->Position
            );
        }

        return $this->Text;
    }

    /**
     * Checks that this token has the specified identifier.
     *
     * @param int $id Identifier to check
     *
     * @return bool true if this is an identifier with the specified text
     */
    public function identifierIs($id)
    {
        return $this->Id == Symbol::IDENTIFIER
            && strcmp($this->Text, $id) == 0;
    }
}
