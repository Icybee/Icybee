<?php

namespace Icybee;

use Brickrouge\Element;

/**
 * A wrapped checkbox that can be easily styled.
 */
class WrappedCheckbox extends Element
{
	private $input;
	private $label;

	public function __construct(array $attributes = [])
	{
		parent::__construct('span', $attributes + [

			Element::CHILDREN => [

				$this->input = new Element(Element::TYPE_CHECKBOX),
				$this->label = new Element('label', [

					Element::INNER_HTML => ''

				])

			],

			'class' => 'wrapped-checkbox',
			'id' => self::auto_element_id()

		]);
	}

	public function offsetSet($attribute, $value)
	{
		if (in_array($attribute, [ 'checked', 'disabled', 'id', 'name', 'value' ]))
		{
			$this->input[$attribute] = $value;

			if ($attribute == 'id')
			{
				$this->label['for'] = $value;
			}
		}

		parent::offsetSet($attribute, $value);
	}

	protected function render_attributes(array $attributes)
	{
		unset($attributes['id']);

		return parent::render_attributes($attributes);
	}
}
