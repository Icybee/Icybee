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

/**
 * Post a form.
 *
 * Note: The form it retrieved by the hook that we attached to the
 * {@link \ICanBoogie\Operation\GetFormEvent} event, just like any other operation.
 */
class PostOperation extends \ICanBoogie\Operation
{
	/**
	 * Controls for the operation: form.
	 *
	 * @see ICanBoogie.Operation::get_controls()
	 */
	protected function get_controls()
	{
		return array
		(
			self::CONTROL_FORM => true
		)

		+ parent::get_controls();
	}

	/**
	 * Returns `true`.
	 *
	 * @see ICanBoogie.Operation::validate()
	 */
	protected function validate(\ICanboogie\Errors $errors)
	{
		return !count($errors);
	}

	/**
	 * Processes the form submission.
	 *
	 * The `finalize` method of the form is used to finalize the operation and obtain a result.
	 * The method is optional, and if the form doesn't define it the value `true` is returned
	 * instead.
	 *
	 * @return mixed The result of the operation.
	 */
	protected function process()
	{
		$form = $this->form;

		return method_exists($form, 'finalize') ? $form->finalize($this) : true;
	}
}