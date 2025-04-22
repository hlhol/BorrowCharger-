<?php
session_start();
$view = new stdClass();
$view->pageTitle = 'Login';
require_once('Views/login.phtml');
