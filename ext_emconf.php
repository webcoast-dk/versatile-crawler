<?php

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Versatile Crawler',
	'description' => 'A versatile and extendable crawler',
	'category' => 'plugin',
	'author' => 'Thorben Nissen',
	'author_email' => 'thorben.nissen@kapp-hamburg.de',
	'author_company' => '',
	'shy' => '',
	'priority' => '',
	'module' => '',
	'state' => 'stable',
	'internal' => '',
	'uploadfolder' => '0',
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'version' => '1.0.0',
	'constraints' => array(
		'depends' => array(
			'typo3' => '8.7.0-8.99.99',
		),
		'conflicts' => array(
		    'crawler' => ''
		),
		'suggests' => array(
		),
	),
);
