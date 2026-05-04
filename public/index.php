<?php
session_start();

require_once '../Core/App.php';
require_once '../Core/Controller.php';
require_once '../Core/Database.php';

define("BASE_URL", '/' . basename(dirname(__DIR__)) . '/public/');

$app = new App();