<?php

namespace OData;

use Data\ArrayList;

/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 18-8-2017
 * Time: 20:24
 */
class ODataToSqlQueryParser
{
    private $model;
    private $currentObject;
    function __construct($model)
    {
        $this->model = $model;
    }

    function parseFilter()
    {
        if ((!isset($_REQUEST['$filter'])) || $_REQUEST['$filter'] == null || $_REQUEST['$filter'] == "") {
            return null;
        }
        $filter = $_REQUEST['$filter'];
        $parser = new Parser($filter);
        return $parser->parseLogicalOrExpression();
    }

    function parseOrderBy()
    {

        $orderByArray = [];
        if ((!isset($_REQUEST['$orderby'])) || $_REQUEST['$orderby'] == null || $_REQUEST['$orderby'] == "") {
            return null;
        }
        $orderBy = $_REQUEST['$orderby'];
        $fields = explode(",", $orderBy);
        foreach ($fields as $field) {
            $fieldOrdering = explode(" ", trim($field));
            $name = $fieldOrdering[0];
            if (count($fieldOrdering) > 1 && count($fieldOrdering) < 3) {
                $orderByArray[] = ["name" => $name, "ordering" => strtolower($fieldOrdering[1]) == 'asc' ? 'asc' : 'desc'];
            } else {
                $orderByArray[] = ["name" => $name, "ordering" => "asc"];
            }
        }
        return $orderByArray;
    }
    public function applyODataToArray($array)
    {
        $list = new ArrayList($array);
        if ($this->parseOrderBy() != null) {
            $this->currentObject = $this->parseOrderBy()[0];
            if ($this->currentObject["ordering"] == "asc") {
                $list = $list->orderBy(function ($obj) {
                    return $obj[$this->currentObject["name"]];
                });
            } else {
                $list = $list->orderByDescending(function ($obj) {
                    return $obj[$this->currentObject["name"]];
                });
            }
        }
        if ($this->parseSkip() != null) {
            $list = $list->skip($this->parseSkip());
        }
        if ($this->parseTop() != null) {
            $list = $list->take($this->parseTop());
        }
        return $list->toArray();
    }
    function parseSelect()
    {
        $selectArray = [];
        if ((!isset($_REQUEST['$select'])) || $_REQUEST['$select'] == null || $_REQUEST['$select'] == "") {
            return null;
        }
        $select = $_REQUEST['$select'];;
        $fields = explode(",", $select);
        foreach ($fields as $field) {
            $selectArray[] = ["name" => trim($field)];
        }
        return $selectArray;
    }

    function parseTop()
    {
        if ((!isset($_REQUEST['$top'])) || $_REQUEST['$top'] == null || (!is_numeric($_REQUEST['$top']))) {
            return null;
        }
        return $_REQUEST['$top'];
    }

    function parseSkip()
    {
        if ((!isset($_REQUEST['$skip'])) || $_REQUEST['$skip'] == null || (!is_numeric($_REQUEST['$skip']))) {
            return null;
        }
        return $_REQUEST['$skip'];
    }

    function totalCount()
    {
        if ((!isset($_REQUEST['$count'])) || $_REQUEST['$count'] == null) {
            return false;
        } else {
            return true;
        }
    }

    function getSelectSql()
    {

        $parsed = $this->parseSelect();
        if (count($parsed) == 0) {
            return "*";
        }

        $dbFields = [];
        foreach ($parsed as $field) {
            $info = new \ClassInfo($this->model);
            foreach ($info->getProperties() as $property) {
                if (strtolower($property->name) == strtolower($field["name"])) {
                    $reflectionProperty = new \PropertyInfo($property->class, $property->name);
                    $attributes = $reflectionProperty->getAttributes();
                    $dbFields[] = str_replace($property->name, $field["name"], $attributes["FieldName"]);
                }
            }
        }
        return count($dbFields) > 0 ? join(", ", $dbFields) : "*";
    }

    function getOrderBySql()
    {
        $parsed = $this->parseOrderBy();
        $dbFields = [];
        if (count($parsed) == 0) {
            return "";
        }
        foreach ($parsed as $field) {
            $info = new \ClassInfo($this->model);
            foreach ($info->getProperties() as $property) {
                if (strtolower($property->name) == strtolower($field["name"])) {
                    $reflectionProperty = new \PropertyInfo($property->class, $property->name);
                    $attributes = $reflectionProperty->getAttributes();
                    $dbFields[] = str_replace($property->name, $field["name"], $attributes["FieldName"]) . ' ' . $field["ordering"];
                }
            }
        }
        return count($dbFields) > 0 ? " ORDER BY " . join(", ", $dbFields) : "";
    }

    function getPagingSql()
    {
        $topParsed = $this->parseTop();
        $skipParsed = $this->parseSkip();
        if ($skipParsed == null && $topParsed == null) {
            return "";
        }
        if ($skipParsed == null) {
            return " LIMIT 0, " . $topParsed;
        } else {
            return " LIMIT " . $skipParsed . ", " . $topParsed;
        }
    }

    function getFilterSql()
    {
        $parsedFilter = $this->parseFilter();
        if ($parsedFilter == null) {
            return "";
        } else {
            $sqlParser = new ExpressionToSql($this->model);
            return " WHERE " . $sqlParser->getSql($parsedFilter);
        }
    }

    function getSql()
    {
        $info = new \ClassInfo($this->model);
        $table = $info->getAttributes()["Table"];
        $countQuery = "SELECT " .
            $this->getSelectSql() .
            " FROM " . $table .
            $this->getFilterSql();
        $query =
            $countQuery .
            $this->getOrderBySql() .
            $this->getPagingSql();
        return ["query" => $query, "countQuery" => $this->totalCount() ? $countQuery : null];
    }
}
