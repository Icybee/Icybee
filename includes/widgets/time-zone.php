<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BrickRouge\Widget;

use BrickRouge\Element;

class TimeZone extends \BrickRouge\Widget
{
	public function __construct($tags=array(), $dummy=null)
	{
		global $document;

		parent::__construct
		(
			'div', $tags
		);

		$document->js->add('time-zone.js');
	}

	public function render_inner_html()
	{
		global $core;

		$offsets = array();
		$list = timezone_identifiers_list();
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
			$date = getdate($now + $offset);

			$options[$offset] = $f->format_datetime($date);
		}

		date_default_timezone_set($tz);

		$this->dataset['offsets'] = $offsets;

		$value = $this->get('value');

		if (!$value)
		{
			$value = $this->get(self::T_DEFAULT);
		}

		if ($value !== null)
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

		if (!$this->get(self::T_REQUIRED))
		{
			$options = array(null => '') + $options;
		}

		$rc .= new Element
		(
			'select', array
			(
				Element::T_OPTIONS => $options,

				'name' => $this->get('name'),
				'value' => $value
			)
		);

		return $rc;
	}
}