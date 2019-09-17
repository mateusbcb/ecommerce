<?php 

require_once("vendor/autoload.php");

use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;


$app = new Slim();

$app->config('debug', true);

$app->get('/', function() {
    
	$page = new Page();
	$page->setTPL("index");

});


$app->get('/admin', function() {
    
	User::verifyLogin();
	$page = new PageAdmin();
	$page->setTpl("index");
});

$app->run();

 ?>