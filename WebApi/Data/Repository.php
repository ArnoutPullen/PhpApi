<?php
/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 18-8-2017
 * Time: 23:34
 */

namespace Data;


class Repository
{
    private $model;
    private $connection;

    function __construct($model, DatabaseConnection $connection)
    {
        $this->connection = $connection;
        $this->model = $model;
    }

    function insert($obj)
    {
        $selectors = [];
        $values = [];
        $info = new \ClassInfo($this->model);
        foreach ($info->getProperties() as $property) {
            $reflectionProperty = new \PropertyInfo($property->class, $property->name);
            $attributes = $reflectionProperty->getAttributes();
            $propertyName = $property->name;
            $fieldName = $attributes["FieldName"];
            if (strtolower($propertyName) != "id") {
                if ($property->getValue($obj) != null) {
                    $selectors[] = $fieldName;
                    $values[] = $property->getValue($obj);
                }
            }
        }
        $table = $info->getAttributes()["Table"];
        return $this->execute(array_merge(["INSERT INTO " . $table . " (" . join(", ", $selectors) . ") VALUES (" . join(", ", array_fill(0, count($values), "?")) . ")"], $values), 2);
    }

    function update($obj)
    {
        $selectors = [];
        $values = [];
        $info = new \ClassInfo($this->model);
        $idField = "id";
        $idFieldName = "Id";
        foreach ($info->getProperties() as $property) {
            $reflectionProperty = new \PropertyInfo($property->class, $property->name);
            $attributes = $reflectionProperty->getAttributes();
            $propertyName = $property->name;
            $fieldName = $attributes["FieldName"];
            if (strtolower($propertyName) != "id") {
                if ($property->getValue($obj) != null) {
                    $selectors[] = $fieldName;
                    $values[] = $property->getValue($obj);
                }
            } else {
                $idField = $propertyName;
                $idFieldName = $fieldName;
            }
        }
        $reflectionProperty = new \PropertyInfo($obj, $idField);
        $values[] = $reflectionProperty->getValue($obj);
        $table = $info->getAttributes()["Table"];
        return $this->execute(array_merge(["UPDATE " . $table . " SET " . (count($selectors) > 1 ? join("=? ", $selectors) : ($selectors[0] . "=?")) . " WHERE " . $idFieldName . "=?"], $values), 2);
    }

    function delete($obj)
    {
        $info = new \ClassInfo($this->model);
        $idField = "id";
        $idFieldName = "Id";
        foreach ($info->getProperties() as $property) {
            $reflectionProperty = new \PropertyInfo($property->class, $property->name);
            $attributes = $reflectionProperty->getAttributes();
            $propertyName = $property->name;
            $fieldName = $attributes["FieldName"];
            if (strtolower($propertyName) == "id") {
                $idField = $propertyName;
                $idFieldName = $fieldName;
            }
        }
        $table = $info->getAttributes()["Table"];
        if (is_numeric($obj) || is_string($obj)) {
            return $this->execute(["DELETE FROM " . $table . " WHERE " . $idFieldName . "=?", $obj], 2);
        } else {
            $reflectionProperty = new \PropertyInfo($obj, $idField);
            $id = $reflectionProperty->getValue($obj);
            return $this->execute(["DELETE FROM " . $table . " WHERE " . $idFieldName . "=?", $id], 2);
        }
    }

    function get($obj)
    {
        $info = new \ClassInfo($this->model);
        $idField = "id";
        $idFieldName = "Id";
        foreach ($info->getProperties() as $property) {
            $reflectionProperty = new \PropertyInfo($property->class, $property->name);
            $attributes = $reflectionProperty->getAttributes();
            $propertyName = $property->name;
            $fieldName = $attributes["FieldName"];
            if (strtolower($propertyName) == "id") {
                $idField = $propertyName;
                $idFieldName = $fieldName;
            }
        }
        $table = $info->getAttributes()["Table"];
        if (is_numeric($obj) || is_string($obj)) {
            return $this->execute(["SELECT * FROM " . $table . " WHERE " . $idFieldName . "=?", $obj], 1);
        } else {
            return $this->execute(["SELECT * FROM " . $table . " WHERE " . $idFieldName . "=?", $obj[$idField]], 1);
        }
    }

    function all($obj)
    {
        $info = new \ClassInfo($this->model);

        $table = $info->getAttributes()["Table"];
        if (is_numeric($obj) || is_string($obj)) {
            return $this->execute(["SELECT * FROM " . $table, $obj], 2);
        } else {
            return $this->execute(["SELECT * FROM " . $table], 2);
        }
    }

    function execute($sql, $resultType)
    {
        if (!is_array($sql)) {
            $sql = [$sql];
        }
        try {
            $statement = call_user_func_array(array($this->connection, "query"), $sql);
            if ($resultType == 0) {
                $result = [];
                while ($o = $statement->fetch()) {
                    $class = new \ClassInfo($this->model);
                    $instance = $class->newInstanceArgs();
                    foreach ($class->getProperties() as $reflectionProperty) {
                        $property = new \PropertyInfo($reflectionProperty->class, $reflectionProperty->name);
                        $fieldName = $property->getAttributes()["FieldName"];
                        if (isset($o[$fieldName])) {
                            $property->setValue($instance, $o[$fieldName]);
                        }
                    }
                    $result[] = $instance;
                }
                return $result;
            } else if ($resultType == 1) {
                if ($o = $statement->fetch()) {
                    $class = new \ClassInfo($this->model);
                    $instance = $class->newInstanceArgs();
                    foreach ($class->getProperties() as $reflectionProperty) {
                        $property = new \PropertyInfo($reflectionProperty->class, $reflectionProperty->name);
                        $fieldName = $property->getAttributes()["FieldName"];
                        if (isset($o[$fieldName])) {
                            $property->setValue($instance, $o[$fieldName]);
                        }
                    }
                    return $instance;
                } else {
                    return null;
                }
            } else if ($resultType == 3) {
                return $statement->rowCount();
            } else {
                $statement->rowCount();
            }
        } catch (\Exception $ex) {
            return false;
        }
        return true;
    }

}