<?php

//OData
require 'OData/Parser.php';
require 'OData/ExpressionLexer.php';
require 'OData/Token.php';
require 'OData/Char.php';
require 'OData/Symbol.php';
require 'OData/KeyWord.php';
require 'OData/BinaryOperatorKind.php';
require 'OData/BinaryExpression.php';
require 'OData/LogicalExpression.php';
require 'OData/LiteralToken.php';
require 'OData/UnaryOperatorKind.php';
require 'OData/UnaryExpression.php';
require 'OData/ExpressionToSql.php';
require 'OData/ODataQueryParser.php';
//Core
require("Core/IController.php");
require("Core/CustomReflection.php");
require("Core/DependencyResolver.php");
require("Core/Router.php");
//Data
require("Data/Repository.php");
require("Data/DatabaseConnection.php");
require("Data/ArrayList.php");
//header('Content-Type: application/json');
use OData\ExpressionToSql;

class WebApi
{
    function __construct()
    {

    }

    function run()
    {
        $router = new Router();

        $router->onRouteFound(function ($controller, $action, $method, $params, $attributes, $headers) {
            $dependencyResolver = new DependencyResolver();
            $controller = $dependencyResolver->resolve($controller);
            $params[] = json_decode(file_get_contents("php://input"));

            if (array_key_exists("authorize", $attributes)) {
                If (!array_key_exists("Authorization", $headers)) {
                    http_response_code(403);
                    die(json_encode(["Error" => "403 UnAuthorized"]));
                }
            }
            //header('Content-Type: application/json');
            echo(json_encode(call_user_func_array(array($controller, $action), $params)));
        });
        $router->onRouteNotFound(function () {
            http_response_code(404);
            die(json_encode(["Error" => "Route Not Found"]));
        });

        $router->run();
    }
}



