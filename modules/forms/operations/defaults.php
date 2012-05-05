<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Forms;

use ICanBoogie\Errors;
use ICanBoogie\Operation;

/**
 * Returns model specific default values for the form.
 */
class DefaultsOperation extends Operation
{
	/**
	 * Controls for the operation: authentication, permission(create)
	 * @see ICanBoogie.Operation::__get_controls()
	 */
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_AUTHENTICATION => true,
			self::CONTROL_PERMISSION => Module::PERMISSION_CREATE
		)

		+ parent::__get_controls();
	}

	/**
	 * Validates the operation unles the operation key is not defined.
	 *
	 * @see ICanBoogie.Operation::validate()
	 */
	protected function validate(Errors $errors)
	{
		if (!$this->key)
		{
			$errors['key'] = 'Missing modelid';

			return false;
		}

		return true;
	}

	/**
	 * The "defaults" operation can be used to retrieve the default values for the form, usualy
	 * the values for the notify feature.
	 *
	 * @see ICanBoogie.Operation::process()
	 */
	protected function process()
	{
		global $core;

		$modelid = $this->key;
		$models = $core->configs->synthesize('formmodels', 'merge');

		if (empty($models[$modelid]))
		{
			\ICanBoogie\log_error("Unknown model");

			return;
		}

		$model = $models[$modelid];
		$model_class = $model['class'];

		if (!method_exists($model_class, 'get_defaults'))
		{
			\ICanBoogie\log_success("Model doesn't have defaults");

			return false;
		}

		return call_user_func(array($model_class, 'get_defaults'));
	}
}