<?php
/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 18-8-2017
 * Time: 18:30
 */

namespace OData;


class ExpressionToSql
{
    private $model;

    function __construct($model)
    {
        $this->model = $model;
    }

    function getSql($expression)
    {
        return $this->parseExpression($expression);
    }

    function parseExpression($expression)
    {
        if ($expression instanceof LiteralToken) {
            return $this->parseLiteral($expression);
        } else if ($expression instanceof BinaryExpression) {
            return $this->parseBinaryExpression($expression);
        } else if ($expression instanceof LogicalExpression) {
            return $this->parseLogicalExpression($expression);
        } else if ($expression instanceof Token) {
            return $this->parseIdentifier($expression);
        } else if ($expression instanceof UnaryExpression) {
            return $this->parseUnaryExpression($expression);
        } else {
            //custom
            $info = new \ClassInfo($this->model);
            foreach ($info->getProperties() as $property) {
                if (strtolower($property->name) == strtolower($expression)) {
                    $refprop = new \PropertyInfo($property->class, $property->name);
                    $attributes = $refprop->getAttributes();
                    return str_replace($property->name, $expression, $attributes["FieldName"]);
                }
            }
            //non custom
            throw new \Exception("Invalid query");
        }
    }

    function parseBinaryExpression($expression)
    {
        switch ($expression->type) {
            case BinaryOperatorKind::Equal:
                return $this->parseExpression($expression->left) . " = " . $this->parseExpression($expression->right);
                break;
            case BinaryOperatorKind::NotEqual:
                return $this->parseExpression($expression->left) . " != " . $this->parseExpression($expression->right);
                break;
            case BinaryOperatorKind::GreaterThan:
                return $this->parseExpression($expression->left) . " > " . $this->parseExpression($expression->right);
                break;
            case BinaryOperatorKind::GreaterThanOrEqual:
                return $this->parseExpression($expression->left) . " >= " . $this->parseExpression($expression->right);
                break;
            case BinaryOperatorKind::LessThan:
                return $this->parseExpression($expression->left) . " < " . $this->parseExpression($expression->right);
                break;
            case BinaryOperatorKind::LessThanOrEqual:
                return $this->parseExpression($expression->left) . " <= " . $this->parseExpression($expression->right);
                break;
            case BinaryOperatorKind::Add:
                return $this->parseExpression($expression->left) . " + " . $this->parseExpression($expression->right);
                break;
            case BinaryOperatorKind::Subtract:
                return $this->parseExpression($expression->left) . " - " . $this->parseExpression($expression->right);
                break;
            case BinaryOperatorKind::Multiply:
                return $this->parseExpression($expression->left) . " * " . $this->parseExpression($expression->right);
                break;
            case BinaryOperatorKind::Divide:
                return $this->parseExpression($expression->left) . " / " . $this->parseExpression($expression->right);
                break;
            case BinaryOperatorKind::Modulo:
                return $this->parseExpression($expression->left) . " % " . $this->parseExpression($expression->right);
                break;
        }
        return "";
    }

    function parseLogicalExpression($expression)
    {
        switch ($expression->type) {
            case BinaryOperatorKind::_And:
                return $this->parseExpression($expression->left) . " and " . $this->parseExpression($expression->right);
                break;
            case BinaryOperatorKind::_Or:
                return $this->parseExpression($expression->left) . " or " . $this->parseExpression($expression->right);
                break;
        }
        return "";
    }

    function parseLiteral($expression)
    {
        return $expression->text;
    }

    function parseIdentifier($expression)
    {
        return $expression->Text;
    }

    function parseUnaryExpression($expression)
    {
        if ($expression->kind == UnaryOperatorKind::Not) {
            return "not " . $this->parseExpression($expression->operand);
        }
        return "";
    }

}