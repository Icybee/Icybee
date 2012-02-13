<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord;

use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\Operation;
use ICanBoogie\Module;
use ICanBoogie\Modules;

use Brickrouge;
use Brickrouge\Button;
use Brickrouge\Element;

class Form extends Node
{
	const MODELID = 'modelid';
	const CONFIG = 'config';
	const BEFORE = 'before';
	const AFTER = 'after';
	const COMPLETE = 'complete';
	const PAGEID = 'pageid';

	public $modelid;
	public $before;
	public $after;
	public $complete;
	public $is_notify;
	public $notify_destination;
	public $notify_from;
	public $notify_bcc;
	public $notify_subject;
	public $notify_template;
	public $pageid;

	protected function __get_model()
	{
		global $core;

		$modelid = $this->modelid;
		$models = $core->configs->synthesize('formmodels', 'merge');

		if (empty($models[$modelid]))
		{
			throw new Exception('Unknown model id: %id', array('%id' => $modelid), 404);
		}

		return $models[$modelid];
	}

	protected function __get_url()
	{
		global $core;

		if (!$this->pageid)
		{
			return '#form-url-not-defined';
		}

		try
		{
			return $core->models['pages'][$this->pageid]->url;
		}
		catch (\Exception $e)
		{
			return '#missing-target-page-' . $this->pageid;
		}
	}

	protected function __get_form()
	{
		$class = $this->model['class'];

		return new $class
		(
			array
			(
				Brickrouge\Form::VALUES => $_REQUEST,
				Brickrouge\Form::HIDDENS => array
				(
					Operation::DESTINATION => 'forms',
					Operation::NAME => Modules\Forms\Module::OPERATION_POST,
					Modules\Forms\Module::OPERATION_POST_ID => $this->nid
				),

				Element::CHILDREN => array
				(
					'#submit' => new Button
					(
						'Send', array
						(
							Element::WEIGHT => 1000,

							'class' => 'btn-primary',
							'type' => 'submit'
						)
					)
				),

				'id' => $this->slug
			)
		);
	}

	public function __toString()
	{
		global $core;

		#
		# if the form was sent successfully, we return the `complete` message instead of the form.
		#

		$session = $core->session;

		if (!empty($session->modules['forms']['rc'][$this->nid]))
		{
			unset($session->modules['forms']['rc'][$this->nid]);

			return '<div id="' . $this->slug . '">' . $this->complete . '</div>';
		}

		try
		{
			$form = $this->form;

			if (isset($form->hiddens[Operation::DESTINATION]) && isset($form->hiddens[Operation::NAME]))
			{
				$destination = $form->hiddens[Operation::DESTINATION];
				$name = $access = $form->hiddens[Operation::NAME];

				if ($name == 'save')
				{
					$access = Module::PERMISSION_CREATE;
				}
				else if ($name == 'post' && $destination == 'forms')
				{
					$access = 'post form';
				}

				if (!$core->user->has_permission($access, $destination))
				{
					return (string) new \Brickrouge\AlertMessage
					(
						<<<EOT
<p>You don't have permission to execute the <q>$name</q> operation on the <q>$destination</q> module,
<a href="{$core->site->path}/admin/users.roles">the <q>{$core->user->role->name}</q> role should be modified</a>.</p>
EOT
						, array(), 'error'
					);
				}
			}

			$core->document->css->add('public/page.css');

			// FIXME-20110531: saving the form disables validation during the "forms/send" operation
			//$this->form->save();

			$before = $this->before;
			$after = $this->after;
			$form = (string) $this->form;

			Event::fire
			(
				'render:before', array
				(
					'before' => &$before,
					'after' => &$after,
					'form' => &$form,
				),

				$this
			);

			if ($before)
			{
				$before = '<div id="before-form-' . $this->slug . '" class="form-before">' . $before . '</div>';
			}

			if ($after)
			{
				$after = '<div id="after-form-' . $this->slug . '" class="form-after">' . $after . '</div>';
			}

			$rc = $before . $form . $after;

			Event::fire
			(
				'render', array
				(
					'rc' => &$rc,
					'before' => $before,
					'after' => $after,
					'form' => $form,
				),

				$this
			);

			return $rc;
		}
		catch (\Exception $e)
		{
			return \ICanBoogie\Debug::format_alert($e);
		}
	}
}