<?php 

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\pages;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Pages();
	$page->setTPL("index");

});

$app->run();

 ?>