<?php
session_start();
$view = new stdClass();
$view->pageTitle = 'Booking';
require_once('Views/RentalUser/Booking.phtml');
