<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Element;

use Brickrouge\Button;
use Brickrouge\Element;

class ActionBarOperations extends Element
{
	private $actions;

	public function __construct(array $attributes)
	{
		parent::__construct('div', $attributes + [

			Element::IS => 'ActionBarOperations',

			Element::CHILDREN => [

				new Element('label', [

					Element::INNER_HTML => '',

					'class' => 'btn-group-label count'

				]),

				$this->actions = new Element('div', [

					'class' => 'btn-group'

				]),

				new Button('Annuler la sélection', [ 'data-dismiss' => 'selection' ])
			],

			'data-actionbar-context' => 'Operation',
			'data-pattern-one' => "Un élément sélectionné",
			'data-pattern-other' => ":count éléments sélectionnés",

			'class' => 'actionbar-actions listview-operations'

		]);
	}

	/**
	 * Returns an empty string if {@link OPTIONS} are empty, otherwise actions are populated
	 * with children.
	 *
	 * @inheritdoc
	 */
	public function render()
	{
		$options = $this[self::OPTIONS];

		if (!$options)
		{
			return '';
		}

		$this->populate_actions($options);

		return parent::render();
	}

	/**
	 * Populates actions with children.
	 *
	 * @param array $jobs
	 */
	protected function populate_actions(array $jobs)
	{
		$children = [];

		foreach ($jobs as $operation => $label)
		{
			$children[] = new Button($label, [

				'data-operation' => $operation,
				'data-target' => 'manager'

			]);
		}

		$this->actions[Element::CHILDREN] = $children;
	}
}
