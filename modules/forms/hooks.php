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

use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\Operation;
use ICanBoogie\Mailer;

class Hooks
{
	public static function event_alter_editor_options(Event $event)
	{
		$event->rc['form'] = t('Form');
	}

	public static function markup_form(array $args, \WdPatron $patron, $template)
	{
		global $core, $page;

		$id = $args['select'];
		$model = $core->models['forms'];

		if (is_numeric($id))
		{
			$form = $model[$id];
		}
		else
		{
			list($conditions, $conditions_args) = $model->parseConditions(array('slug' => $id, 'language' => $page->language));

			$form = $model->where(implode(' AND ', $conditions), $conditions_args)->one;
		}

		if (!$form)
		{
			throw new Exception('Unable to retrieve form using supplied conditions: %conditions', array('%conditions' => json_encode($args['select'])));
		}

		Event::fire
		(
			'nodes_load', array
			(
				'nodes' => array($form)
			),

			$patron
		);

		if (!$form->is_online)
		{
			throw new Exception('The form %title is offline', array('%title' => $form->title));
		}

		return (string) $form;
	}

	/**
	 * Tries to load the form associated with the operation.
	 *
	 * This function is a callback for the `ICanBoogie\Operation::get_form` event.
	 *
	 * The OPERATION_POST_ID parameter provides the key of the form active record to load.
	 *
	 * If the form is successfully retrieved a callback is added to the
	 * "<operation_class>::process" event, it is used to send a notify message with the parameters
	 * provided by the form active record.
	 *
	 * @param Event $event
	 * @param Operation $operation
	 */
	public static function on_operation_get_form(Event $event, Operation $operation)
	{
		global $core;

		$request = $event->request;

		if (!$request[Module::OPERATION_POST_ID])
		{
			return;
		}

		$record = $core->models['forms'][(int) $request[Module::OPERATION_POST_ID]];
		$form = $record->form;

		$event->rc = $form;

		Event::add
		(
			get_class($operation) . '::process', function(Event $event, Operation $operation) use ($record, $form, $core)
			{
				$rc = $event->rc;
				$bind = $event->request->params;
				$template = $record->notify_template;
				$mailer = null;

				$mailer_tags = array
				(
					Mailer::T_BCC => $record->notify_bcc,
					Mailer::T_DESTINATION => $record->notify_destination,
					Mailer::T_FROM => $record->notify_from,
					Mailer::T_SUBJECT => $record->notify_subject,
					Mailer::T_MESSAGE => null
				);

				if (method_exists($form, 'alter_notify'))
				{
					$form->alter_notify
					(
						(object) array
						(
							'rc' => &$rc,
							'bind' => &$bind,
							'template' => &$template,
							'mailer' => &$mailer,
							'mailer_tags' => &$mailer_tags
						),

						$record, $event, $operation
					);
				}

				$core->session->modules['forms']['rc'][$record->nid] = $rc;

				if ($record->is_notify)
				{
					$patron = new \WdPatron();

					if (!$mailer_tags[Mailer::T_MESSAGE])
					{
						$mailer_tags[Mailer::T_MESSAGE] = $template;
					}

					foreach ($mailer_tags as &$value)
					{
						$value = $patron($value, $bind);
					}

					if (!$mailer)
					{
						$mailer = new Mailer($mailer_tags);
					}

					wd_log('operation send mailer: \1', array($mailer));

					$mailer();
				}
			}
		);
	}
}