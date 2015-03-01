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

use ICanBoogie\I18n;

/**
 * Decorates a component with a _filter_ element.
 *
 * @TODO-20130627: create a _real_ decorator, with a component.
 */
class FilterDecorator
{
	private $record;
	private $property;
	private $filtering;
	private $label;
	private $value;

	/**
	 * Constructor.
	 *
	 * @param \ICanBoogie\ActiveRecord $record
	 * @param string $property The property to filter.
	 * @param bool $filtering
	 * @param null|string $label Defines the label for the filter link. If null the value of the
	 * property is used instead. If the value of the property is used it is escaped using the
	 * {@link \ICanBoogie\escape()} function, otherwise the label is use as is.
	 * @param null $value
	 */
	public function __construct($record, $property, $filtering, $label=null, $value=null)
	{
		$this->record = $record;
		$this->property = $property;
		$this->filtering = $filtering;
		$this->label = $label;
		$this->value = $value;
	}

	public function render()
	{
		$property = $this->property;
		$value = $this->value;
		$label = $this->label;

		if ($value === null)
		{
			$value = $this->record->$property;
		}

		if ($label === null)
		{
			$label = \ICanBoogie\escape($value);
		}

		if ($this->filtering)
		{
			return $label;
		}

		$title = \ICanBoogie\escape(I18n\t('Display only: :identifier', [ ':identifier' => strip_tags($label) ]));
		$url = \ICanBoogie\escape($property . '=') . urlencode($value);

		return <<<EOT
<a class="filter" href="?$url" title="$title">$label</a>
EOT;
	}

	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (\Exception $e)
		{
			return \Brickrouge\render_exception($e);
		}
	}
}
