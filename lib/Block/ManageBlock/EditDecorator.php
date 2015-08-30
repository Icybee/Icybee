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

use ICanBoogie\Accessor\AccessorTrait;
use ICanBoogie\AppAccessor;
use ICanBoogie\I18n;

use Brickrouge\A;
use Brickrouge\Decorator;

/**
 * Decorates a component with an _edit_ element.
 *
 * @property-read \ICanBoogie\Core|\Icybee\Binding\CoreBindings $app
 */
class EditDecorator extends Decorator
{
	use AccessorTrait;
	use AppAccessor;

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

		return new A($component, $this->app->url_for("admin:{$model->id}:edit", $record), [

			'class' => 'edit',
			'title' => $this->app->translate('manage.edit')

		]);
	}
}
