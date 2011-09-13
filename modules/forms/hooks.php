<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Hooks;

use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\Module;
use ICanBoogie\Operation;
use ICanBoogie\Mailer;

class Forms
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
	 * @param Event $event
	 * @param Operation $operation
	 */
	public static function on_operation_get_form(Event $event, Operation $operation)
	{
		global $core;

		$params = $event->params;

		if (empty($params[Module\Forms::OPERATION_SEND_ID]))
		{
			return;
		}

		$record = $core->models['forms'][(int) $params[Module\Forms::OPERATION_SEND_ID]];
		$form = $record->form;

		$event->rc = $form;

		Event::add
		(
			get_class($operation) . '::process', function(Event $event, Operation $operation) use ($record, $form)
			{
				global $core;

				$template = $record->notify_template;
				$message = null;
				$bind = $operation->params;

				$rc = $event->rc;

				if (method_exists($form, 'alter_notify'))
				{
					$form->alter_notify
					(
						(object) array
						(
							'rc' => &$rc,
							'message' => &$message,
							'template' => &$template,
							'bind' => &$bind
						)
					);
				}

				$core->session->modules['forms']['rc'][$record->nid] = $rc;

				if ($record->is_notify)
				{
					$patron = new \WdPatron();
					$message = $message ?: $patron($template, $bind);

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
			}
		);
	}
}