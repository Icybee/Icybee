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

	public function __construct(array $attributes=array())
	{
		parent::__construct('div', $attributes);
	}

	public function render_inner_html()
	{
		global $core;

		$offsets = array();
		$list = timezone_identifiers_list();
		$groups = array();

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
		$html .= '<option>&nbsp;</option>';

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

		return new Element
		(
			'select', array
			(
				Element::INNER_HTML => $html,

				'name' => $this['name']
			)
		);
		/*















		$utc_time = new \DateTime('now', new \DateTimezone('UTC'));

		foreach ($list as $zone)
		{
			$tz_value = new \DateTimeZone($zone);
			$offset = $tz_value->getOffset($utc_time);

			$offsets[$offset][] = $zone;
		}

		ksort($offsets);

		foreach ($offsets as $offset => $names)
		{
			sort($names);

			$offsets[$offset] = $names;
		}

		$now = time();
		$options = array();
		$f = $core->locale->date_formatter;

		$tz = date_default_timezone_get();
		date_default_timezone_set('GMT');

		foreach ($offsets as $offset => $zones)
		{
			$options[$offset] = $f->format_datetime($now + $offset);
		}

		date_default_timezone_set($tz);

		$this->dataset['offsets'] = $offsets;

		$value = $this['value'];

		if ($value === null)
		{
			$value = $this[self::DEFAULT_VALUE];
		}

		if ($value)
		{
			$offset = null;
			$zone = null;

			if (is_numeric($value))
			{
				$offset = $value;
			}
			else
			{
				$tz_value = new \DateTimeZone($value);
				$offset = $tz_value->getOffset($utc_time);
				$zone = $value;
				$value = $offset;
			}

			$this->dataset['zone'] = $zone;
		}

		$rc = parent::render_inner_html();

		if (!$this[self::REQUIRED])
		{
			$options = array(null => '') + $options;
		}

		$rc .= new Element
		(
			'select', array
			(
				Element::OPTIONS => $options,

				'name' => $this['name'],
				'value' => $value
			)
		);

		return $rc;
		*/
	}
}