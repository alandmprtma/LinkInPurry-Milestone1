<?php

require_once '../core/Router.php';
require_once '../app/controllers/HomeController.php';
require_once '../app/controllers/AboutController.php';

// Inisialisasi router
$router = new Router();

// Daftarkan route dan callback-nya
$router->add('/', [new HomeController(), 'index']);
$router->add('/about', [new AboutController(), 'index']);

// Jalankan router
$router->run();
?>
