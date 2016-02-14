<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Brickrouge\Widget;

use Brickrouge\Element;

class TimeZone extends \Brickrouge\Element
{
	/*
	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->js->add('time-zone.js');
	}
	*/

	public function __construct(array $attributes = array())
	{
		parent::__construct('div', $attributes);
	}

	public function render_inner_html()
	{
		$list = timezone_identifiers_list();
		$groups = [];

		foreach ($list as $name)
		{
			if ($name == 'UTC')
			{
				continue;
			}

			list($group) = explode('/', $name, 2);

			$groups[$group][] = $name;
		}

		$value = $this['value'];

		if ($value === null)
		{
			$value = $this[self::DEFAULT_VALUE];
		}

		$html = '';
		$html .= '<option value="">&nbsp;</option>';

		foreach ($groups as $group => $timezones)
		{
			$group_strlen = strlen($group) + 1;
			$html .= '<optgroup label="' . $group . '">';

			foreach ($timezones as $timezone)
			{
				$html .= '<option value="' . $timezone . '"';

				if ($timezone === $value)
				{
					$html .= ' selected="selected"';
				}

				$html .= '>' . substr($timezone, $group_strlen) . '</option>';
			}

			$html .= '</optgroup>';
		}

		return new Element('select', [

			Element::INNER_HTML => $html,

			'name' => $this['name'],
			'class' => 'form-control'

		]);
	}
}
