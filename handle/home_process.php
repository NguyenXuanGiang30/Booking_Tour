<?php
/**
 * Home page process
 */

require_once __DIR__ . '/../functions/tour_function.php';

$featuredTours = get_featured_tours(6);

require_once __DIR__ . '/../views/home.php';
