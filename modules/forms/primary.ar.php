<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Forms;

use ICanBoogie\Debug;
use ICanBoogie\Exception;
use ICanBoogie\Operation;

use Brickrouge\Button;
use Brickrouge\Element;

class Form extends \Icybee\Modules\Nodes\Node
{
	const MODELID = 'modelid';
	const CONFIG = 'config';
	const BEFORE = 'before';
	const AFTER = 'after';
	const COMPLETE = 'complete';
	const PAGEID = 'pageid';

	const FORM_RECORD_TAG = '#form-record';

	public $modelid;

	/**
	 * The optional message that appears before the form.
	 *
	 * @var string
	 */
	public $before;

	/**
	 * The optional message that appears after the formm.
	 *
	 * @var string
	 */
	public $after;

	/**
	 * The message that appears instead of the form, when the form was successfuly submitted.
	 *
	 * @var string
	 */
	public $complete;

	/**
	 * `true` if the notify options are enabled.
	 *
	 * @var bool
	 */
	public $is_notify;
	public $notify_destination;
	public $notify_from;
	public $notify_bcc;
	public $notify_subject;
	public $notify_template;
	public $pageid;

	protected function get_model()
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

	protected function get_url()
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

	/**
	 * Returns the {@link \Brickrouge\Form} associated with the active record.
	 *
	 * @return \Brickrouge\Form
	 */
	protected function get_form()
	{
		$class = $this->model['class'];

		return new $class
		(
			array
			(
				\Brickrouge\Form::ACTIONS => new Button
				(
					'Send', array
					(
						'class' => 'btn-primary',
						'type' => 'submit'
					)
				),

				\Brickrouge\Form::HIDDENS => array
				(
					Operation::DESTINATION => 'forms',
					Operation::NAME => Module::OPERATION_POST,
					Module::OPERATION_POST_ID => $this->nid
				),

				\Brickrouge\Form::VALUES => $_POST + $_GET,

				self::FORM_RECORD_TAG => $this,

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
					return (string) new \Brickrouge\Alert
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
			$form = $this->form;

			new Form\BeforeRenderEvent
			(
				$this, array
				(
					'before' => &$before,
					'after' => &$after,
					'form' => $form,
				)
			);

			$normalized = \ICanBoogie\normalize($this->slug);

			if ($before)
			{
				$before = '<div class="form-before form-before--' . $normalized . '">' . $before . '</div>';
			}

			if ($after)
			{
				$after = '<div class="form-after form-after--' . $normalized . '">' . $after . '</div>';
			}

			$html = $before . $form . $after;

			new Form\RenderEvent
			(
				$this, array
				(
					'html' => &$html,
					'before' => $before,
					'after' => $after,
					'form' => $form,
				)
			);

			return $html;
		}
		catch (\Exception $e)
		{
			Debug::report($e);

			return Debug::format_alert($e);
		}
	}
}

namespace Icybee\Modules\Forms\Form;

/**
 * Event class for the `Icybee\Modules\Forms\Form::render:before` event.
 */
class BeforeRenderEvent extends \ICanBoogie\Event
{
	/**
	 * The form to render.
	 *
	 * @var \Icybee\Modules\Forms\Form
	 */
	public $form;

	/**
	 * The HTML content before the form.
	 *
	 * @var string
	 */
	public $before;

	/**
	 * The HTML content after the form.
	 *
	 * @var string
	 */
	public $after;

	/**
	 * The event is created with the type `render:before`.
	 *
	 * @param \Icybee\Modules\Forms\Form $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\Modules\Forms\Form $target, array $properties)
	{
		parent::__construct($target, 'render:before', $properties);
	}
}

/**
 * Event class for the `Icybee\Modules\Forms\Form::render` event.
 */
class RenderEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the HTML resulting of the rendering.
	 *
	 * @var string
	 */
	public $html;

	/**
	 * The form to render.
	 *
	 * @var \Icybee\Modules\Forms\Form
	 */
	public $form;

	/**
	 * The HTML content before the form.
	 *
	 * @var string
	 */
	public $before;

	/**
	 * The HTML content after the form.
	 *
	 * @var string
	 */
	public $after;

	/**
	 * The event is created with the type `render`.
	 *
	 * @param \Icybee\Modules\Forms\Form $target
	 * @param array $properties
	 */
	public function __construct(\Icybee\Modules\Forms\Form $target, array $properties)
	{
		parent::__construct($target, 'render', $properties);
	}
}