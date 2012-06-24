<?php

$path = __DIR__;

#
# La classe WdCore, qui nous permet d'instancier l'objet `core`, coeur du framework. Il ne servira
# pas à grand chose, mais au moins le framework sera basiquement configuré, notamment l'autoloader.
#

require_once dirname($path) . '/wdcore/wdcore.php';

$core = new WdCore();

$db = new WdDatabase
(
	'mysql:dbname=weirdog', 'root', '^love:mysql$', array
	(
		'#charset' => 'latin1',
		'#collate' => 'latin1_swedish_ci'
	)
);

$model = new WdModel
(
	array
	(
		Model::T_CONNECTION => $db,
		Model::T_NAME => 'charset_test',
		Model::T_SCHEMA => array
		(
			'fields' => array
			(
				'title' => 'varchar'
			)
		)
	)
);

if (!$model->isInstalled())
{
	$model->install();
}

$i = 10;
$str = "L'été est là !";

while ($i--)
{
	$model->save(array('title' => str_shuffle($str)));
}

$records = $model->all;

var_dump($records);