<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Operation\Forms;

use ICanBoogie\Exception;
use ICanBoogie\Mailer;
use ICanBoogie\Module;
use ICanBoogie\Operation;

class Post extends Operation
{
	/**
	 * Controls for the operation: form.
	 *
	 * @see ICanBoogie.Operation::__get_controls()
	 */
	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_RECORD => true,
			self::CONTROL_FORM => true
		)

		+ parent::__get_controls();
	}

	/**
	 * Returns the record for the operation.
	 *
	 * The OPERATION_POST_ID is required in the request's params to retrieve the corresponding
	 * form record.
	 *
	 * @see ICanBoogie.Operation::__get_record()
	 *
	 * @return ICanBoogie\ActiveRecord\Form The operation record.
	 */
	protected function __get_record()
	{
		$request = $this->request;

		if (empty($request[Module\Forms::OPERATION_POST_ID]))
		{
			throw new Exception('Missing OPERATION_POST_ID parameter', array(), 404);
		}

		$form_id = (int) $request[Module\Forms::OPERATION_POST_ID];

		return $this->module->model[$form_id];
	}

	protected function __get_form()
	{
		return $this->record->form;
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		return true;
	}

	/**
	 * Sends the form.
	 *
	 * The operation object is altered by setting the following properties:
	 *
	 * 1. `notify_template`: The template used to create the notify message.
	 * 2. `notify_bind`: The bind used to resolve the notify template.
	 * 3. `notify_message`: The message resulting from the template resolving. This property is
	 * only set when the notify message has been sent.
	 *
	 *
	 * The `finalize` method
	 * =====================
	 *
	 * If defined, the `finalize` method of the form's model is invoked with the operation object
	 * as argument. Before the `finalize` method is invoked, the operation object is altered by
	 * adding the `notify_template` and `notify_bind` properties.
	 *
	 * The value of the `notify_template` property is set to the value of the form record
	 * `notify_template` property. The value of the property is used as template to format the
	 * message to send. One can overrite the value of the property to use a template different then
	 * the one defined by the form record.
	 *
	 * The value of the `notify_bind` property is set to the value of the request `params`
	 * property. The `notify_bind` property is used as scope to format the template of the message
	 * to send. One can overrite the value of the property to use a different scope.
	 *
	 * If the result of the `finalize` method and the `is_notify` property of the record are not
	 * empty, an email is sent using the `notify_<identifier>` properties. The properties are
	 * resolved using the `Patron()` function and the request's params, or, if defined, the
	 * value of the `entry` property of the operation object, as bind.
	 *
	 * If the `notify_message` property of the operation object is defined, it's used for the
	 * email's message, otherwise a message is created by resolving the record's `notify_template`
	 * property's value.
	 *
	 *
	 * Result tracking
	 * ===============
	 *
	 * The result of the "send" operation is stored in the session under
	 * "[modules][forms][rc][<record_nid>]". This stored value is used when the form is
	 * rendered to choose what to render. For example, if the value is empty, the form is rendered
	 * with the _before_ and _after_ messages, otherwise only the _complete_ message is rendered.
	 *
	 * Note: If the form's model class doesn't provied a `finalize` method, the result of the
	 * operation is always `true`.
	 *
	 * @return mixed The result of the operation is empty if the operation failed.
	 */
	protected function process()
	{
		global $core;

		$record = $this->record;
		$form = $this->form;

		$this->notify_template = $record->notify_template;
		$this->notify_bind = $this->request->params;

		// TODO-20110921: see on_operation_get_form(), the process should be the same.

		$rc = method_exists($form, 'finalize') ? $form->finalize($this) : true;
		$core->session->modules['forms']['rc'][$record->nid] = $rc;

		if ($rc && $record->is_notify)
		{
			$patron = new \WdPatron();
			$bind = $this->notify_bind;
			$message = isset($this->notify_message) ? $this->notify_message : $patron($this->notify_template, $bind);

			$mailer = new Mailer
			(
				array
				(
					Mailer::T_DESTINATION => $patron($record->notify_destination, $bind),
					Mailer::T_FROM => $patron($record->notify_from, $bind),
					Mailer::T_BCC => $patron($record->notify_bcc, $bind),
					Mailer::T_SUBJECT => $patron($record->notify_subject, $bind),
					Mailer::T_MESSAGE => $message
				)
			);

			wd_log('operation send mailer: \1', array($mailer));

			$mailer();
		}

		return $rc;
	}
}