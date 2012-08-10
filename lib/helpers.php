<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee;

/**
 * Renders CSS class names into a string suitable for the HTML `class` attribute.
 *
 * @param array $names CSS class names.
 * @param string|array $modifiers CSS class names modifiers.
 *
 * @return string
 */
function render_css_class(array $names, $modifiers=null)
{
	$names = array_filter($names);

	if ($modifiers)
	{
		if (is_string($modifiers))
		{
			$modifiers = explode(' ', $modifiers);
		}

		$modifiers = array_map('trim', $modifiers);
		$modifiers = array_filter($modifiers);

		foreach ($modifiers as $k => $modifier)
		{
			if ($modifier{0} == '-')
			{
				unset($names[substr($modifier, 1)]);
				unset($modifiers[$k]);
			}
		}

		if ($modifiers)
		{
			$names = array_intersect_key($names, array_combine($modifiers, $modifiers));
		}
	}

	array_walk($names, function(&$v, $k) {

		if ($v === true) $v = $k;

	});

	return implode(' ', $names);
}