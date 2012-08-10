<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Pages;

use Brickrouge\Element;

class NavigationElement extends Element
{
	static protected function builder(array $tree, $depth=1)
	{
		$html = '';

		foreach ($tree as $branch)
		{
			$record = $branch->record;
			$class = $record->css_class('-constructor -slug');

			$html .=  $class ? '<li class="' . $class . '">' : '<li>';
			$html .= '<a href="' . $record->url . '">' . $record->label . '</a>';

			if ($branch->children)
			{
				$html .= self::builder($branch->children, $depth + 1);
			}

			$html .= '</li>';
		}

		return '<ol class="' . ($depth == 1 ? 'nav' : 'dropdown-menu') . ' lv' . $depth . '">' . $html . '</ol>';
	}

	public function __construct()
	{

	}
}