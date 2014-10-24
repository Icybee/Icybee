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

use Brickrouge\Element;

/**
 * Decorates a component with an _edit_ element.
 */
class EditDecorator extends \Brickrouge\Decorator
{
	private $record;

	public function __construct($component, $record)
	{
		parent::__construct($component);

		$this->record = $record;
	}

	public function render()
	{
		$component = parent::render();
		$record = $this->record;
		$model = $record->model;
		$primary = $model->primary;

		return new Element
		(
			'a', array
			(
				Element::INNER_HTML => \Brickrouge\escape($component),

				'class' => 'edit',
				'href' => \ICanBoogie\Routing\contextualize("/admin/{$model->id}/{$record->$primary}/edit"),
				'title' => I18n\t('manage.edit')
			)
		);
	}
}