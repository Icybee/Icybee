<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Element;

use Brickrouge\Element;
use Brickrouge\ElementIsEmpty;

/**
 * A toolbar of the action bar.
 *
 * The `alter_buttons` event is fired to alter buttons before the inner HTML of the toolbar is
 * rendered.
 */
class ActionBarToolbar extends Element
{
	public function __construct(array $attributes = [])
	{
		parent::__construct('div', $attributes + [ 'class' => 'actionbar-toolbar btn-toolbar' ]);
	}

	protected function render_inner_html()
	{
		$buttons = $this->collect();

		new ActionBarToolbar\CollectEvent($this, [ 'buttons' => &$buttons ]);

		if (empty($buttons))
		{
			throw new ElementIsEmpty;
		}

		return implode($buttons);
	}

	protected function collect()
	{
		return [];
	}
}
