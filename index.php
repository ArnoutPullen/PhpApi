<?php
//CORS
header("Access-Control-Allow-Origin: *");
//WebApi Core
require("WebApi/WebApi.php");
//Data
require('Controllers/EventController.php');
require('Repositories/EventRepository.php');
require('Models/Event.php');

$webApi = new WebApi();
$webApi->run();
