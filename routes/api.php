<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/machines', 'MachineController@getAllMachines');
$router->get('machines/{machineId}/products', 'ProductController@getMachineProducts');

$router->post('/machines/{machineId}/insert-coin', 'MachineController@insertCoin');
$router->post('/machines/{machineId}/select-product', 'ProductController@selectProduct');
