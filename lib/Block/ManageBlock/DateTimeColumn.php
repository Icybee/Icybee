<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Block\ManageBlock;

use ICanBoogie\ActiveRecord\Query;
use ICanBoogie\DateTime;

use Icybee\Block\ManageBlock;

/**
 * Representation of a _datetime_ column.
 */
class DateTimeColumn extends Column
{
	use CriterionColumnTrait;

	public function __construct(\Icybee\Block\ManageBlock $manager, $id, array $options = [])
	{
		parent::__construct($manager, $id, $options + [

			'class' => 'date',
			'default_order' => -1,
			'discreet' => true

		]);
	}

	private $discreet_value;

	/**
	 * Renders the datetime.
	 *
	 * If the column is discreet, the repeating dates are replaced by the discreet placeholder
	 * while the time is still displayed.
	 *
	 * @inheritdoc
	 */
	public function render_cell($record)
	{
		$date = $record->{ $this->id };

		if (!$date)
		{
			return null;
		}

		if (!($date instanceof DateTime))
		{
			$date = new DateTime($date, 'utc');
		}

		if ($date->is_empty)
		{
			return null;
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
	 * @param DateTime $date
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
	 * @param DateTime $date
	 * @param string $property
	 *
	 * @return string
	 */
	protected function render_cell_date($date, $property)
	{
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

		$parts = [

			[ $year, $year ],
			[ $date->format('m'), $date->format('Y-m') ],
			[ $date->format('d'), $date->as_date ]

		];

		$today = new DateTime('now', 'utc');
		$today_year = $today->year;
		$today_month = $today->month;
		$today_day = $today->day;
		$today_formatted = $today->as_date;

		$select = $parts[2][1];

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
				$ttl = $this->manager->t('Display only: :identifier', [ ':identifier' => $label ]);

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
					$ttl = $this->manager->t('Display only: :identifier', [ ':identifier' => $select ]);

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
