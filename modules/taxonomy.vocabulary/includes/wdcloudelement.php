<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Brickrouge\Element;

class WdCloudElement extends Element
{
	const T_LEVELS = '#cloud-levels';

	protected function render_inner_html()
	{
		$options = $this[self::OPTIONS];

		if (!$options)
		{
			return;
		}

		$min = min($options);
    	$max = max($options);

    	$range = ($min == $max) ? 1 : $max - $min;
    	$levels = $this[self::T_LEVELS] ?: 8;

		$markup = $this->type == 'ul' ? 'li' : 'span';

		$rc = '';

		foreach ($options as $name => $usage)
		{
			$popularity = ($usage - $min) / $range;
			$level = 1 + ceil($popularity * ($levels - 1));

			$rc .= '<' . $markup . ' class="tag' . $level . '">' . $name . '</' . $markup . '>' . PHP_EOL;
		}

		return $rc;
	}
}