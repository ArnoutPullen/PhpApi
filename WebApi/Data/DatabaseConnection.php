<?php
/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 18-8-2017
 * Time: 23:38
 */

namespace Data;


class DatabaseConnection extends \PDO
{

    const PARAM_host = 'localhost';
    const PARAM_port = '3306';
    const PARAM_db_name = 'test';
    const PARAM_user = 'root';
    const PARAM_db_pass = '';

    public function __construct($options = null)
    {
        parent::__construct('mysql:host=' . DatabaseConnection::PARAM_host . ';port=' . DatabaseConnection::PARAM_port . ';dbname=' . DatabaseConnection::PARAM_db_name,
            DatabaseConnection::PARAM_user,
            DatabaseConnection::PARAM_db_pass, $options);
    }

    public function query($query)
    { //secured query with prepare and execute
        $args = func_get_args();
        array_shift($args); //first element is not an argument but the query itself, should removed

        $reponse = parent::prepare($query);
        $reponse->execute($args);
        return $reponse;
    }

    public function insecureQuery($query)
    { //you can use the old query at your risk ;) and should use secure quote() function with it
        return parent::query($query);
    }

}
