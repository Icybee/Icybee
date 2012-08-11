<?php

require_once dirname(__DIR__) . '/wdcore/wdcore.php';

$core = new WdCore();
$path = __DIR__;

$connection = new WdDatabase("sqlite:$path/models-and-records.sq3");

$model = new WdModel
(
	array
	(
		Model::T_CONNECTION => $connection,
		Model::T_NAME => 'node',
		Model::T_SCHEMA => array
		(
			'fields' => array
			(
				'id' => 'serial',
				'title' => array('varchar', 80),
				'number' => array('integer', 'unsigned' => true)
			)
		)
	)
);

$model_extended = new WdModel
(
	array
	(
		Model::T_CONNECTION => $connection,
		Model::T_EXTENDS => $model,
		Model::T_NAME => 'contents',
		Model::T_SCHEMA => array
		(
			'fields' => array
			(
				'body' => 'text'
			)
		)
	)
);

if (!$model->is_installed())
{
	$model->install();
}

if (!$model_extended->is_installed())
{
	$model_extended->install();
}

$i = 10;

while ($i--)
{
	$number = uniqid();

	$model->save
	(
		array
		(
			'title' => "title-$i-" . md5($number),
			'number' => $number
		)
	);
}

$i = 10;

while ($i--)
{
	$number = uniqid();

	$model_extended->save
	(
		array
		(
			'title' => "title-$i-" . md5($number),
			'number' => $number,
			'body' => str_repeat(sha1($number), 3)
		)
	);
}

$records = $model->all;

var_dump($records);

$records = $model_extended->all;

var_dump($records);