<?php

namespace Test;

use ICanBoogie\ActiveRecord\Model;
use ICanBoogie\HTTP\Request;

require_once $_SERVER['DOCUMENT_ROOT'] . '/Icybee/Icybee.php';

class ActiveRecord extends \ICanBoogie\ActiveRecord
{
	public $id;
	public $name;
	public $is_active;

	public function save()
	{
		$model = $this->_model;
		$primary = $model->primary;

		$properties = get_object_vars($this);
		$key = null;

		if (isset($properties[$primary]))
		{
			$key = $properties[$primary];

			unset($properties[$primary]);
		}

		/*
		 * We discart null values so that we don't have to define every properties before saving
		 * our active record.
		 *
		 * FIXME-20110904: we should check if the schema allows the column value to be null
		 */

		foreach ($properties as $identifier => $value)
		{
			if ($value !== null && $identifier{0} != '_')
			{
				continue;
			}

			unset($properties[$identifier]);
		}

		$path = '/api/test';
		$method = 'put';

		if ($key)
		{
			$path .= '/' . $key;
			$method = Request::METHOD_POST;
		}

		$request = Request::from
		(
			array
			(
				'path' => $path
			)
		);

		$response = $request->$method($properties);

		$this->$primary = $response->rc['key'];

		var_dump($this);
	}
}

class Operation extends \ICanBoogie\Operation\ActiveRecord\Save
{
	protected function get_record()
	{
		global $model;

		return $model[$this->key];
	}

	protected function process()
	{
		global $model;

		return array('key' => $model->save($this->request->params, $this->key), 'mode' => 'insert');
	}
}

$name = 'test_' . md5(__FILE__);

$model = new Model
(
	array
	(
		Model::T_ACTIVERECORD_CLASS => 'Test\ActiveRecord',
		Model::T_CONNECTION => $core->db,
		Model::T_NAME => $name,
		Model::T_SCHEMA => array
		(
			'fields' => array
			(
				'id' => 'serial',
				'name' => array('varchar', 32),
				'is_active' => 'boolean'
			)
		)
	)
);

if (!$model->is_installed())
{
	$model->install();
}

\ICanBoogie\Route::add
(
	'/api/test/:key', array
	(
		'callback' => function($request) use($model)
		{
			$operation = new Operation($request);
			$operation->key = $request['key'];

			return $operation;
		},

		'via' => Request::METHOD_POST
	)
);

$ar = $model[4];
$ar->name = 'lady gaga';
$ar->save();

		/*
$ar = ActiveRecord::from
(
	array
	(
		'name' => 'madonna',
		'is_active' => false
	),

	array($model)
);

$ar->save();
*/