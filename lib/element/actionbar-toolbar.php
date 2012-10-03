<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Admin\Element;

use ICanBoogie\Event;
use Brickrouge\Element;

/**
 * A toolbar of the action bar.
 *
 * The `alter_buttons` event is fired to alter buttons before the inner HTML of the toolbar is
 * rendered.
 */
class ActionbarToolbar extends Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct('div', $attributes + array('class' => 'actionbar-toolbar btn-toolbar'));
	}

	protected function render_inner_html()
	{
		$buttons = $this->collect_buttons();

		$this->fire_alter_buttons(array('buttons' => &$buttons));

		if (empty($buttons))
		{
			throw new \Brickrouge\ElementIsEmpty;
		}

		return implode($buttons);
	}

	protected function collect_buttons()
	{
		return array();
	}

	protected function fire_alter_buttons(array $params)
	{
		Event::fire('alter_buttons', $params, $this);
	}
}