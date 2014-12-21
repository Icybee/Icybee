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

use Brickrouge\Button;
use Brickrouge\Popover;
use Brickrouge\Widget;

class Get extends \ICanBoogie\Operation
{
	private $widget_class;

	protected function get_controls()
	{
		return [

			self::CONTROL_AUTHENTICATION => true

		] + parent::get_controls();
	}

	protected function validate(\ICanboogie\Errors $errors)
	{
		$this->widget_class = $class = 'Brickrouge\Widget\\' . \ICanBoogie\camelize(strtr($this->request['class'], '-', '_'));

		if (!class_exists($class, true))
		{
			throw new \Exception(\ICanBoogie\format('Unknown widget class: %class', array('%class' => $class)));
		}

		return true;
	}

	protected function process()
	{
		$app = $this->app;

		if (!$app->user_id)
		{
			throw new \Exception('Unauthorized', 401);
		}

		$user = $app->user;

		if ($user->language)
		{
			$app->locale = $user->language;
		}

		$request = $this->request;
		$document = $app->document;

		$rc = null;
		$mode = $request['mode'];
		$selected = $request['selected'];

		if ($request['value'])
		{
			$selected = $request['value'];
		}

		$class = $this->widget_class;

		$el = new $class([

			'value' => $selected,
			Widget\AdjustNode::T_CONSTRUCTOR => $request['constructor']

		]);

		if (!$mode)
		{
			$rc = (string) $el;
		}
		else if ($mode == 'popup')
		{
			$rc = (string) new Popover([

				Popover::ACTIONS => [

					new Button('Cancel', [ 'data-action' => 'cancel' ]),
					new Button('Remove', [ 'data-action' => 'remove', 'class' => 'btn-danger' ]),
					new Button('Use', [ 'data-action' => 'use', 'class' => 'btn-primary' ])

				],

				Popover::FIT_CONTENT => true,

				Popover::INNER_HTML => $el,

				'class' => 'popover popover--' . \ICanBoogie\normalize($this->request['class']) . ' contrast'

			]);
		}
		else if ($mode == 'results')
		{
			$rc = $el->get_results($_GET);
		}
		else if ($mode)
		{
			throw new \Exception(\ICanBoogie\format('Unknown widget mode: %mode', [ '%mode' => $mode ]));
		}

		$this->response['assets'] = $document->assets;
		$this->response['mode'] = $mode;

		return $rc;
	}
}
