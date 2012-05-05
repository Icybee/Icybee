<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Operation\Widget;

use ICanBoogie\Exception;
use ICanBoogie\Operation;

use Brickrouge\Button;
use Brickrouge\Popover;
use Brickrouge\Widget;

class Get extends Operation
{
	private $widget_class;

	protected function __get_controls()
	{
		return array
		(
			self::CONTROL_AUTHENTICATION => true
		)

		+ parent::__get_controls();
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		$this->widget_class = $class = 'Brickrouge\Widget\\' . \ICanBoogie\camelize('-' . $this->request['class'], '-');

		if (!class_exists($class, true))
		{
			throw new Exception('Unknown widget class: %class', array('%class' => $class));
		}

		return true;
	}

	protected function process()
	{
		global $core, $document;

		if (!$core->user_id)
		{
			throw new Exception('Unauthorized', array(), 401);
		}

		$user = $core->user;

		if ($user->language)
		{
			$core->language = $user->language;
		}

		$request = $this->request;
		$document = $core->document;

		$rc = null;
		$mode = $request['mode'];
		$selected = $request['selected'];

		if ($request['value'])
		{
			$selected = $request['value'];
		}

		$class = $this->widget_class;

		$el = new $class
		(
			array
			(
				'value' => $selected,
				Widget\AdjustNode::T_CONSTRUCTOR => $request['constructor']
			)
		);

		if (!$mode)
		{
			$rc = (string) $el;
		}
		else if ($mode == 'popup')
		{
			$rc = (string) new Popover
			(
				array
				(
					Popover::ACTIONS => array
					(
						new Button('Cancel', array('data-action' => 'cancel')),
						new Button('Remove', array('data-action' => 'remove', 'class' => 'btn-danger')),
						new Button('Use', array('data-action' => 'use', 'class' => 'btn-primary'))
					),

					Popover::FIT_CONTENT => true,

					Popover::INNER_HTML => $el,

					'class' => 'popover popover--' . \ICanBoogie\normalize($this->request['class']) . ' contrast'
				)
			);
		}
		else if ($mode == 'results')
		{
			$rc = $el->get_results($_GET);
		}
		else if ($mode)
		{
			throw new Exception('Uknown widget mode: %mode', array('%mode' => $mode));
		}

		$this->response['assets'] = $document->assets;
		$this->response['mode'] = $mode;

		return $rc;
	}
}