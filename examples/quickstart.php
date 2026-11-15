<?php
declare(strict_types=1);
require dirname(__DIR__).'/vendor/autoload.php';
use Aymudo\Client;
$aymudo=Client::fromEnvironment();
$capabilities=$aymudo->capabilities();$operations=$aymudo->operations();
printf("Workspace exposes %d operations.\n",count($operations['operations']??[]));print_r($capabilities);
