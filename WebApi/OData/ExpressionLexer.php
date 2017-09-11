<?php
namespace OData;

/**
 * Class ExpressionLexer
 *
 * Lexical analyzer for Astoria URI expression parsing
 * Literals        Representation
 * --------------------------------------------------------------------
 * Null            null
 * Boolean         true | false
 * Int32           (digit+)
 * Int64           (digit+)(L|l)
 * Decimal         (digit+ ['.' digit+])(M|m)
 * Float (Single)  (digit+ ['.' digit+][e|E [+|-] digit+)(f|F)
 * Double          (digit+ ['.' digit+][e|E [+|-] digit+)
 * String          "'" .* "'"
 * DateTime        datetime"'"dddd-dd-dd[T|' ']dd:mm[ss[.fffffff]]"'"
 * Binary          (binary|X)'digit*'
 * GUID            guid'digit*
 *
 */
class ExpressionLexer
{

    /**
     * Text being parsed
     *
     * @var char[]
     */
    private $_text;

    /**
     * Length of text being parsed
     *
     * @var int
     */
    private $_textLen;

    /**
     * Position on text being parsed
     *
     * @var int
     */
    private $_textPos;

    /**
     * Character being processed
     *
     * @var char
     */
    private $_ch;

    /**
     * Token being processed
     *
     * @var Token
     */
    private $_token;

    /**
     * Initialize a new instance of ExpressionLexer
     *
     * @param string $expression Expression to parse
     */
    public function __construct($expression)
    {
        $this->_text = $expression;
        $this->_textLen = strlen($this->_text);
        $this->_token = new Token();
        $this->_setTextPos(0);
        $this->nextToken();
    }

    /**
     * To get the expression token being processed
     *
     * @return Token
     */
    public function getCurrentToken()
    {
        return $this->_token;
    }

    public function getNextToken()
    {
        return $this->_token;
    }


    /**
     * To set the token being processed
     *
     * @param Token $token The expression token to set as current
     *
     * @return void
     */
    public function setCurrentToken($token)
    {
        $this->_token = $token;
    }

    /**
     * To get the text being parsed
     *
     * @return string
     */
    public function getExpressionText()
    {
        return $this->_text;
    }

    /**
     * Position of the current token in the text being parsed
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->_token->Position;
    }

    /**
     * Whether the specified token identifier is a numeric literal
     *
     * @param int $id Token identifier to check
     *
     * @return bool true if it's a numeric literal; false otherwise
     */
    public static function isNumeric($id)
    {
        return
            $id == Symbol::INTEGER_LITERAL
            || $id == Symbol::DECIMAL_LITERAL
            || $id == Symbol::DOUBLE_LITERAL
            || $id == Symbol::INT64_LITERAL
            || $id == Symbol::SINGLE_LITERAL;
    }

    /**
     * Reads the next token, skipping whitespace as necessary.
     *
     * @return void
     */
    public function nextToken()
    {

        while (Char::isWhiteSpace($this->_ch)) {
            $this->_nextChar();
        }

        $t = null;
        $tokenPos = $this->_textPos;
        switch ($this->_ch) {
            case '(':
                $this->_nextChar();
                $t = Symbol::OPENPARAM;
                break;
            case ')':
                $this->_nextChar();
                $t = Symbol::CLOSEPARAM;
                break;
            case ',':
                $this->_nextChar();
                $t = Symbol::COMMA;
                break;
            case '-':
                $hasNext = $this->_textPos + 1 < $this->_textLen;
                if ($hasNext && Char::isDigit($this->_text[$this->_textPos + 1])) {
                    $this->_nextChar();
                    $t = $this->_parseFromDigit();
                    if (self::isNumeric($t)) {
                        break;
                    }

                    $this->_setTextPos($tokenPos);
                } else if ($hasNext && $this->_text[$tokenPos + 1] == 'I') {
                    $this->_nextChar();
                    $this->_parseIdentifier();
                    $this->_setTextPos($tokenPos);
                }

                $this->_nextChar();
                $t = Symbol::MINUS;
                break;
            case '=':
                $this->_nextChar();
                $t = Symbol::EQUAL;
                break;
            case '/':
                $this->_nextChar();
                $t = Symbol::SLASH;
                break;
            case '?':
                $this->_nextChar();
                $t = Symbol::QUESTION;
                break;
            case '.':
                $this->_nextChar();
                $t = Symbol::DOT;
                break;
            case '\'':
                $quote = $this->_ch;
                do {
                    $this->_nextChar();
                    while ($this->_textPos < $this->_textLen && $this->_ch != $quote) {
                        $this->_nextChar();
                    }

                    if ($this->_textPos == $this->_textLen) {
                        new \Exception("expressionLexerUnterminatedStringLiteral");
                    }

                    $this->_nextChar();
                } while ($this->_ch == $quote);
                $t = Symbol::STRING_LITERAL;
                break;
            case '*':
                $this->_nextChar();
                $t = Symbol::STAR;
                break;

            case ';':
                $this->_nextChar();
                $t = Symbol::COLON;
                break;
            default:
                if (Char::isLetter($this->_ch) || $this->_ch == '_') {
                    $this->_parseIdentifier();
                    $t = Symbol::IDENTIFIER;
                    break;
                }

                if (Char::isDigit($this->_ch)) {
                    $t = $this->_parseFromDigit();
                    break;
                }

                if ($this->_textPos == $this->_textLen) {
                    $t = Symbol::END;
                    break;
                }
                new \Exception("expressionLexerInvalidCharacter");
        }

        $this->_token->Id = $t;
        $this->_token->Text = substr($this->_text, $tokenPos, $this->_textPos - $tokenPos);
        $this->_token->Position = $tokenPos;

        // Handle type-prefixed literals such as binary, datetime or guid.
        $this->_handleTypePrefixedLiterals();

        // Handle keywords.
        if ($this->_token->Id == Symbol::IDENTIFIER) {
            if ($this->_token->Text == KeyWord::TRUE
                || $this->_token->Text == KeyWord::FALSE
            ) {
                $this->_token->Id = Symbol::BOOLEAN_LITERAL;
            } else if ($this->_token->Text == KeyWord::NULL) {
                $this->_token->Id = Symbol::NULL_LITERAL;
            }
        }
    }

    /**
     * Returns the next token without advancing the lexer to next token
     *
     * @return Token
     */
    public function peekNextToken()
    {
        $savedTextPos = $this->_textPos;
        $savedChar = $this->_ch;
        $savedToken = clone $this->_token;
        $this->nextToken();
        $result = clone $this->_token;
        $this->_textPos = $savedTextPos;
        $this->_ch = $savedChar;
        $this->_token->Id = $savedToken->Id;
        $this->_token->Position = $savedToken->Position;
        $this->_token->Text = $savedToken->Text;
        return $result;
    }

    /**
     * Validates the current token is of the specified kind
     *
     * @param int $tokenId Expected token kind
     *
     * @return void
     *
     * @throws \Exception if current token is not of the
     *                        specified kind.
     */
    public function validateToken($tokenId)
    {
        if ($this->_token->Id != $tokenId) {
            throw  new \Exception("expressionLexerSyntaxError");
        }
    }

    /**
     * Starting from an identifier, reads alternate sequence of dots and identifiers
     * and returns the text for it
     *
     * @return string The dotted identifier starting at the current identifier
     */
    public function readDottedIdentifier()
    {
        $this->validateToken(Symbol::IDENTIFIER);
        $identifier = $this->_token->Text;
        $this->nextToken();
        while ($this->_token->Id == Symbol::DOT) {
            $this->nextToken();
            $this->validateToken(Symbol::IDENTIFIER);
            $identifier = $identifier . '.' . $this->_token->Text;
            $this->nextToken();
        }

        return $identifier;
    }


    /**
     * Handles the literals that are prefixed by types.
     * This method modified the token field as necessary.
     *
     * @return void
     *
     * @throws \Exception
     */
    private function _handleTypePrefixedLiterals()
    {
        $id = $this->_token->Id;
        if ($id != Symbol::IDENTIFIER) {
            return;
        }

        $quoteFollows = $this->_ch == '\'';
        if (!$quoteFollows) {
            return;
        }

        $tokenText = $this->_token->Text;

        if (strcasecmp('datetime', $tokenText) == 0) {
            $id = Symbol::DATETIME_LITERAL;
        } else if (strcasecmp('guid', $tokenText) == 0) {
            $id = Symbol::GUID_LITERAL;
        } else if (strcasecmp('binary', $tokenText) == 0
            || strcasecmp('X', $tokenText) == 0
            || strcasecmp('x', $tokenText) == 0
        ) {
            $id = Symbol::BINARY_LITERAL;
        } else {
            return;
        }

        $tokenPos = $this->_token->Position;
        do {
            $this->_nextChar();
        } while ($this->_ch != '\0' && $this->_ch != '\'');

        if ($this->_ch == '\0') {
            throw  new \Exception("expressionLexerUnterminatedStringLiteral");
        }

        $this->_nextChar();
        $this->_token->Id = $id;
        $this->_token->Text
            = substr($this->_text, $tokenPos, $this->_textPos - $tokenPos);
    }

    /**
     * Parses a token that starts with a digit
     *
     * @return int The kind of token recognized.
     */
    private function _parseFromDigit()
    {
        $result = null;
        $startChar = $this->_ch;
        $this->_nextChar();
        if ($startChar == '0' && $this->_ch == 'x' || $this->_ch == 'X') {
            $result = Symbol::BINARY_LITERAL;
            do {
                $this->_nextChar();
            } while (ctype_xdigit($this->_ch));
        } else {
            $result = Symbol::INTEGER_LITERAL;
            while (Char::isDigit($this->_ch)) {
                $this->_nextChar();
            }

            if ($this->_ch == '.') {
                $result = Symbol::DOUBLE_LITERAL;
                $this->_nextChar();
                $this->_validateDigit();

                do {
                    $this->_nextChar();
                } while (Char::isDigit($this->_ch));
            }

            if ($this->_ch == 'E' || $this->_ch == 'e') {
                $result = Symbol::DOUBLE_LITERAL;
                $this->_nextChar();
                if ($this->_ch == '+' || $this->_ch == '-') {
                    $this->_nextChar();
                }

                $this->_validateDigit();
                do {
                    $this->_nextChar();
                } while (Char::isDigit($this->_ch));
            }

            if ($this->_ch == 'M' || $this->_ch == 'm') {
                $result = Symbol::DECIMAL_LITERAL;
                $this->_nextChar();
            } else if ($this->_ch == 'd' || $this->_ch == 'D') {
                $result = Symbol::DOUBLE_LITERAL;
                $this->_nextChar();
            } else if ($this->_ch == 'L' || $this->_ch == 'l') {
                $result = Symbol::INT64_LITERAL;
                $this->_nextChar();
            } else if ($this->_ch == 'f' || $this->_ch == 'F') {
                $result = Symbol::SINGLE_LITERAL;
                $this->_nextChar();
            }
        }

        return $result;
    }

    /**
     * Parses an identifier by advancing the current character.
     *
     * @return void
     */
    private function _parseIdentifier()
    {
        do {
            $this->_nextChar();
        } while (Char::isLetterOrDigit($this->_ch) || $this->_ch == '_');
    }

    /**
     * Advance to next character.
     *
     * @return void
     */
    private function _nextChar()
    {
        if ($this->_textPos < $this->_textLen) {
            $this->_textPos++;
        }

        $this->_ch
            = $this->_textPos < $this->_textLen
            ? $this->_text[$this->_textPos] : '\0';
    }

    /**
     * Set the text position.
     *
     * @param int $pos Value to position.
     *
     * @return void
     */
    private function _setTextPos($pos)
    {
        $this->_textPos = $pos;
        $this->_ch
            = $this->_textPos < $this->_textLen
            ? $this->_text[$this->_textPos] : '\0';
    }

    /**
     * Validate current character is a digit.
     *
     * @return void
     *
     * @throws \Exception
     */
    private function _validateDigit()
    {
        if (!Char::isDigit($this->_ch)) {
            throw  new \Exception("expected digit");
        }
    }

}