<?php

require_once '../core/Router.php';
require_once '../app/controllers/HomeJSController.php';
require_once '../app/controllers/HomeCController.php';

// Inisialisasi router
$router = new Router();

// Daftarkan route dan callback-nya
$router->add('/', [new HomeJSController(), 'index']);
$router->add('/about', [new HomeCController(), 'index']);

// Jalankan router
$router->run();
?>
