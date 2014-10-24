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

use Brickrouge\Element;
use Brickrouge\ElementIsEmpty;

class ActionbarSearch extends Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct('div', $attributes + array('class' => 'actionbar-search'));
	}

	protected function render_inner_html()
	{
		$html = parent::render_inner_html();

		new ActionbarSearch\AlterInnerHTMLEvent($this, array('html' => &$html));

		if (empty($html))
		{
			throw new ElementIsEmpty;
		}

		return $html;
	}
}

namespace Icybee\Element\ActionbarSearch;

/**
 * Event class for the `Icybee\Element\ActionbarSearch::alter_inner_html` event.
 */
class AlterInnerHTMLEvent extends \ICanBoogie\Event
{
	/**
	 * Reference to the rendered inner HTML of the element.
	 *
	 * @var string
	 */
	public $html;

	/**
	 * The event is constructed with the type `alter_inner_html`.
	 *
	 * @param \Icybee\Element\ActionbarSearch $target
	 * @param array $payload
	 */
	public function __construct(\Icybee\Element\ActionbarSearch $target, array $payload)
	{
		parent::__construct($target, 'alter_inner_html', $payload);
	}
}