<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
 */

$router->get('upload/', 'CleansingController@index');
$router->post('upload/', 'CleansingController@upload');
$router->get('upload/records', 'CleansingController@records');

$router->get('upload/batchjobs', 'CleansingController@batch');
$router->post('upload/batchjob', 'CleansingController@batchjob');
