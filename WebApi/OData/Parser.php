<?php

namespace OData;

/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 29-7-2017
 * Time: 15:27
 */
class Parser
{

    private $_lexer;

    private $parameters;

    function __construct($expressionText)
    {
        $expressionText = str_replace("\'", "'", $expressionText);
        $this->_lexer = new ExpressionLexer($expressionText);
    }

    function parse()
    {
    }

    function parseLogicalOrExpression()
    {
        $this->rEnter("parseLogicalOrExpression");
        $left = $this->parseLogicalAndExpression();
        while ($this->isKeyWord(KeyWord::_OR)) {
            $this->_lexer->nextToken();
            $right = $this->parseLogicalAndExpression();
            $left = new LogicalExpression(BinaryOperatorKind::_Or, $left, $right);
        }
        return $left;
    }

    function parseLogicalAndExpression()
    {
        $this->rEnter("parseLogicalAndExpression");
        $left = $this->parseBinaryExpression();
        while ($this->isKeyWord(KeyWord::_AND)) {
            $this->_lexer->nextToken();
            $right = $this->parseBinaryExpression();
            $left = new LogicalExpression(BinaryOperatorKind::_And, $left, $right);
        }
        return $left;
    }

    function parseBinaryExpression()
    {
        $this->rEnter("parseBinaryExpression");
        $left = $this->parseAdditive();
        $type = null;
        while (true) {
            if ($this->isKeyWord(KeyWord::EQUAL)) {
                $type = BinaryOperatorKind::Equal;
            } else if ($this->isKeyWord(KeyWord::NOT_EQUAL)) {
                $type = BinaryOperatorKind::NotEqual;
            } else if ($this->isKeyWord(KeyWord::GREATERTHAN)) {
                $type = BinaryOperatorKind::GreaterThan;
            } else if ($this->isKeyWord(KeyWord::GREATERTHAN_OR_EQUAL)) {
                $type = BinaryOperatorKind::GreaterThanOrEqual;
            } else if ($this->isKeyWord(KeyWord::LESSTHAN)) {
                $type = BinaryOperatorKind::LessThan;
            } else if ($this->isKeyWord(KeyWord::LESSTHAN_OR_EQUAL)) {
                $type = BinaryOperatorKind::LessThanOrEqual;
            } else {
                break;
            }
            $this->_lexer->nextToken();
            $right = $this->parseAdditive();
            $left = new BinaryExpression($type, $left, $right);
        }

        return $left;
    }

    function parseAdditive()
    {
        $this->rEnter("parseAdditive");
        $left = $this->parseMultiplicative();
        while ($this->isKeyWord(KeyWord::ADD) || $this->isKeyWord(KeyWord::SUB)) {
            $type = null;
            if ($this->isKeyWord(KeyWord::ADD)) {
                $type = BinaryOperatorKind::Add;
            } else {
                $type = BinaryOperatorKind::Subtract;
            }
            $this->_lexer->nextToken();
            $right = $this->parseMultiplicative();
            $left = new BinaryExpression($type, $left, $right);
        }
        return $left;
    }

    function parseMultiplicative()
    {
        $this->rEnter("parseMultiplicative");
        $left = $this->parseUnary();
        while ($this->isKeyWord(KeyWord::MULTIPLY) || $this->isKeyWord(KeyWord::DIVIDE) || $this->isKeyWord(KeyWord::MODULO)) {
            $type = null;
            if ($this->isKeyWord(KeyWord::MULTIPLY)) {
                $type = BinaryOperatorKind::Multiply;
            } else if ($this->isKeyWord(KeyWord::DIVIDE)) {
                $type = BinaryOperatorKind::Divide;
            } else {
                $type = BinaryOperatorKind::Modulo;
            }
            $this->_lexer->nextToken();
            $right = $this->parseUnary();
            $left = new BinaryExpression($type, $left, $right);
        }
        return $left;
    }

    function parseUnary()
    {
        $this->rEnter("parseUnary");
        if ($this->_lexer->getCurrentToken()->Id == Symbol::MINUS || $this->isKeyWord(KeyWord::NOT)) {
            $operator = $this->_lexer->getCurrentToken();
            $this->_lexer->nextToken();
            if ($operator->Id == Symbol::MINUS && ExpressionLexer::isNumeric($this->_lexer->getCurrentToken()->Id)) {
                $numberLiteral = $this->_lexer->getCurrentToken();
                $numberLiteral->Text = "-" . $numberLiteral->Text;
                $numberLiteral->Position = $operator->Position;
                $this->_lexer->setCurrentToken($numberLiteral);
                return $this->parsePrimary();
            }
            $operand = $this->parseUnary();
            $kind = null;
            if ($operator->Id == Symbol::MINUS) {
                $kind = UnaryOperatorKind::Negate;
            } else {
                $kind = UnaryOperatorKind::Not;
            }
            return new UnaryExpression($kind, $operand);
        }
        return $this->parsePrimary();
    }

    function parsePrimary()
    {
        $this->rEnter("parsePrimary");
        $expr = null;
        if ($this->_lexer->getCurrentToken()->Id == Symbol::SLASH) {
            $expr = $this->parseSegment(null);
        } else {
            $expr = $this->parsePrimaryStart();
        }
        while (true) {
            if ($this->_lexer->getCurrentToken()->Id == Symbol::SLASH) {
                $this->_lexer->nextToken();
                if ($this->isKeyWord(KeyWord::ANY)) {
                    $expr = $this->parseAny($expr);
                } else if ($this->isKeyWord(KeyWord::ALL)) {
                    $expr = $this->parseAll($expr);
                } else if ($this->_lexer->peekNextToken()->Id == Symbol::SLASH) {
                    $expr = $this->parseSegment($expr);
                } else {
                    // echo "func";
                    return $this->_lexer->getCurrentToken();
                    /*
                     *  IdentifierTokenizer identifierTokenizer = new IdentifierTokenizer(this.parameters, new FunctionCallParser(this.lexer, this));
                        expr = identifierTokenizer.ParseIdentifier(expr);
                     * */
                }
            } else {
                break;
            }
        }
        $this->_lexer->nextToken();
        return $expr;
    }
    function parseContains()
    {
        $this->_lexer->nextToken();
        if ($this->_lexer->getCurrentToken()->Id != Symbol::OPENPARAM) {
            die("expected )");
            throw new \Exception("expected (");
        }
        $this->_lexer->nextToken();
        $identifier = $this->_lexer->getCurrentToken()->getIdentifier();
        $this->_lexer->nextToken();
        if ($this->_lexer->getCurrentToken()->Id != Symbol::COMMA) {
            throw new \Exception("expected ,");
        }
        $this->_lexer->nextToken();
        $string = $this->_lexer->getCurrentToken();
        if ($string->Id != Symbol::STRING_LITERAL) {
            throw new \Exception("expected String");
        }
        $contains = new ContainsFunction($identifier, $string->Text);
        $this->_lexer->nextToken();
        if ($this->_lexer->getCurrentToken()->Id != Symbol::CLOSEPARAM) {
            die("expected )");
            throw new \Exception("expected )");
        }
        return  $contains;
    }
    function parseAny($parent)
    {
        return $this->parseAnyAll($parent, true);
    }

    function parseAll($parent)
    {
        return $this->parseAnyAll($parent, false);
    }

    function parseAnyAll($parent, $isAny)
    {
        $this->rEnter("parseAnyAll");
        $this->_lexer->nextToken();
        if ($this->_lexer->getCurrentToken()->Id != Symbol::OPENPARAM) {
            throw new \Exception("Unexpected )");
        }
        $this->_lexer->nextToken();
        if ($this->_lexer->getCurrentToken()->Id == Symbol::CLOSEPARAM) {
            $this->_lexer->nextToken();
            if ($isAny) {
                return new AnyToken(new LiteralToken(true, "True"), null, $parent);
            } else {
                return new AllToken(new LiteralToken(true, "True"), null, $parent);
            }
        }
        $parameter = $this->_lexer->getCurrentToken()->getIdentifier();
        $this->parameters[] = $parameter;
        $this->_lexer->nextToken();
        $this->_lexer->validateToken(Symbol::COLON);
        $this->_lexer->nextToken();
        $expr = $this->parseLogicalOrExpression();
        if ($this->_lexer->getCurrentToken()->Id != Symbol::CLOSEPARAM) {
            throw new \Exception("Unexpected )");
        }
        $this->_lexer->nextToken();
        if ($isAny) {
            return new AnyToken($expr, null, $parent);
        } else {
            return new AllToken($expr, null, $parent);
        }
    }

    function parseSegment($parent)
    {
        $this->rEnter("parseSegment");
        $propertyName = $this->_lexer->getCurrentToken()->getIdentifier();
        $this->_lexer->nextToken();
        if (in_array($propertyName, $this->parameters) && $parent == null) {
            return new RangeVariableToken($propertyName);
        }
        return new InnerPathToken($propertyName, $parent, null);
    }

    function parsePrimaryStart()
    {
        $this->rEnter("parsePrimaryStart");
        switch ($this->_lexer->getCurrentToken()->Id) {
            case Symbol::IDENTIFIER:
                if ($this->isKeyWord(KeyWord::CONTAINS)) {
                    return $this->parseContains();
                } else {
                    return $this->_lexer->getCurrentToken()->getIdentifier();
                }
                break;
            case Symbol::OPENPARAM:
                return $this->parseParenExpression();
                break;
            case Symbol::STAR:
                throw new \Exception("Not implemented");
                break;
            default:
                return new LiteralToken($this->_lexer->getCurrentToken()->Id, $this->_lexer->getCurrentToken()->Text);
                break;
        }
    }

    function parseParenExpression()
    {
        $this->rEnter("parseParenExpression");
        if ($this->_lexer->getCurrentToken()->Id != Symbol::OPENPARAM) {
            throw new \Exception("Expected (");
        }
        $this->_lexer->nextToken();
        $expr = $this->parseLogicalOrExpression();
        if ($this->_lexer->getCurrentToken()->Id != Symbol::CLOSEPARAM) {
            throw new \Exception("Expected )");
        }
        $this->_lexer->nextToken();
        return $expr;
    }

    function rEnter($name)
    {
        //  echo $name . PHP_EOL
    }

    function isKeyWord($id)
    {
        return $this->_lexer->getCurrentToken()->identifierIs($id);
    }
}
