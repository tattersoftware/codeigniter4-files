<?php

// Routes to Files controller
$routes->group('files', ['namespace' => 'Tatter\Files\Controllers'], function($routes)
{
	$routes->get('/',                'Files::index');
	$routes->get('index/(:any)',     'Files::index/$1');
	$routes->get('user/(:any)',      'Files::user/$1');
	$routes->get('manage/(:any)',    'Files::manage/$1');
	$routes->get('thumbnail/(:num)', 'Files::thumbnail/$1');

	$routes->post('upload',          'Files::upload');
	$routes->add('export/(:any)',    'Files::export/$1');
});
