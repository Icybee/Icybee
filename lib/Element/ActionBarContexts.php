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

class ActionBarContexts extends Element
{
	public function __construct(array $attributes = [])
	{
		parent::__construct('div', $attributes + [ 'class' => 'actionbar-contexts' ]);
	}

	protected function render_inner_html()
	{
		$items = [];

		new ActionBarContexts\CollectItemsEvent($this, $items);

		$html = implode($items);

		if (empty($html))
		{
			throw new ElementIsEmpty;
		}

		return $html;
	}
}
