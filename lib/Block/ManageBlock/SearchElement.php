<?php

namespace Icybee\Block\ManageBlock;

use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Text;

class SearchElement extends Element
{
	private $q;

	public function __construct(array $attributes = [])
	{
		return parent::__construct('div', [

			Element::CHILDREN => [

				'q' => $this->q = new Text([

					'title' => "Search in the records",
					'size' => '16',
					'class' => 'search',
					'tabindex' => 0,
					'placeholder' => "Search"

				]),

				new Button('', [

					'type' => 'button',
					'class' => 'icon-remove'

				])

			],

			'class' => 'listview-search'
		]);
	}

	public function offsetSet($attribute, $value)
	{
		if (in_array($attribute, [ 'title', 'placeholder', 'value' ]))
		{
			$this->q[$attribute] = $value;
		}

		parent::offsetSet($attribute, $value);
	}
}
