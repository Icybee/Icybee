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
use BrickRouge\Widget;

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
		$this->widget_class = $class = 'BrickRouge\Widget\\' . wd_camelize('-' . $this->request['class'], '-');

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
			$label_cancel = t('label.cancel');
			$label_use = t('label.use');
			$label_remove = t('label.remove');

			$rc = <<<EOT
<div class="popup">

$el

<div class="confirm">
<button type="button" class="cancel">$label_cancel</button>
<button type="button" class="none warn">$label_remove</button>
<button type="button" class="continue">$label_use</button>
</div>

<div class="arrow"><div>&nbsp;</div></div>

</div>
EOT;
		}
		else if ($mode == 'results')
		{
			$rc = $el->get_results($_GET);
		}
		else if ($mode)
		{
			throw new Exception('Uknown widget mode: %mode', array('%mode' => $mode));
		}

		$this->response['assets'] = $document->get_assets();
		$this->response['mode'] = $mode;

		return $rc;
	}
}