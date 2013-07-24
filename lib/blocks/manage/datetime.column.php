<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\ManageBlock;

use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\DateTime;
use Icybee\ManageBlock;

/**
 * Representation of a _datetime_ column.
 */
class DateTimeColumn extends Column
{
	public function __construct(\Icybee\ManageBlock $manager, $id, array $options=array())
	{
		parent::__construct
		(
			$manager, $id, $options + array
			(
				'class' => 'date',
				'default_order' => -1,
				'discreet' => true
			)
		);
	}

	public function alter_query_with_filter(Query $query, $filter_value)
	{
		if ($filter_value)
		{
			$field = $this->id;

			list($year, $month, $day) = explode('-', $filter_value) + array(0, 0, 0);

			if ($year)
			{
				$query->where("YEAR(`$field`) = ?", (int) $year);
			}

			if ($month)
			{
				$query->where("MONTH(`$field`) = ?", (int) $month);
			}

			if ($day)
			{
				$query->where("DAY(`$field`) = ?", (int) $day);
			}
		}

		return $query;
	}

	private $discreet_value;

	/**
	 * Renders the datetime.
	 *
	 * If the column is discreet, the repeating dates are replaced by the discreet placeholder
	 * while the time is still displayed.
	 */
	public function render_cell($record)
	{
		$date = $record->{ $this->id };

		if (!($date instanceof DateTime))
		{
			$date = new DateTime($date, 'utc');
		}

		if ($date->is_empty)
		{
			return;
		}

		if ($this->discreet && $this->discreet_value == $date->as_date)
		{
			$rendered_date = ManageBlock::DISCREET_PLACEHOLDER;
		}
		else
		{
			$rendered_date = $this->render_cell_date($date, $this->id);

			$this->discreet_value = $date->as_date;
		}

		$rendered_time = $this->render_cell_time($date, $this->id);

		return $rendered_date . ($rendered_time ? '&nbsp;<span class="small light">' . $rendered_time . '</span>' : '');
	}

	/**
	 * Renders cell value as time.
	 *
	 * @param \ICanBoogie\ActiveRecord $record
	 * @param string $property
	 *
	 * @return string
	 */
	protected function render_cell_time($date, $property)
	{
		return $date->local->format('H:i');
	}

	/**
	 * Renders cell value as date.
	 *
	 * @param \ICanBoogie\ActiveRecord $record
	 * @param string $property
	 *
	 * @return string
	 */
	protected function render_cell_date($date, $property)
	{
		$tag = $property;

		$year = $date->year;
		$month = $date->month;
		$day = $date->day;

		$filtering = false;
		$filter = null;

		if ($this->manager->is_filtering($property))
		{
			$filtering = true;
			$filter = $this->manager->options->filters[$property]; // TODO-20130621: provide a get_filter_value() method
		}

		$parts = array
		(
			array($year, $year),
			array($date->format('m'), $date->format('Y-m')),
			array($date->format('d'), $date->as_date)
		);

		$today = new DateTime('now', 'utc');
		$today_year = $today->year;
		$today_month = $today->month;
		$today_day = $today->day;
		$today_formatted = $today->as_date;

		$select = $parts[2][1];
		$diff_days = $day - $today_day;

		if ($year == $today_year && $month == $today_month && $day <= $today_day && $day > $today_day - 6)
		{
			$label = \ICanBoogie\I18n\date_period($date);
			$label = ucfirst($label);

			if ($filtering && $filter == $today_formatted)
			{
				$rc = $label;
			}
			else
			{
				$ttl = $this->manager->t('Display only: :identifier', array(':identifier' => $label));

				$rc = <<<EOT
<a href="?$property=$select" title="$ttl" class="filter">$label</a>
EOT;
			}
		}
		else
		{
			$rc = '';

			foreach ($parts as $i => $part)
			{
				list($value, $select) = $part;

				if ($filtering && $filter == $select)
				{
					$rc .= $value;
				}
				else
				{
					$ttl = $this->manager->t('Display only: :identifier', array(':identifier' => $select));

					$rc .= <<<EOT
<a class="filter" href="?$property=$select" title="$ttl">$value</a>
EOT;
				}

				if ($i < 2)
				{
					$rc .= '–';
				}
			}
		}

		return $rc;
	}
}

/**
 * Representation of a _date_ column.
 */
class DateColumn extends DateTimeColumn
{
	protected function render_cell_time($date, $property)
	{
		return;
	}
}