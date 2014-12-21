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

class Group extends \Brickrouge\Group
{
	public function __construct(array $attributes=[])
	{
		parent::__construct($attributes);

		$this->tag_name = 'div';
		$this->add_class('group');
		$this->add_class('clearfix');
	}

	protected function render_group_legend($legend)
	{
		return '<div class="group-legend">' . $legend . '</div>';
	}

	/**
	 * Adds the `enabled` class name if the element has the `group-toggler` class name and a
	 * checked checkbox child.
	 */
	protected function render_class(array $class_names)
	{
		if (!empty($class_names['group-toggler']))
		{
			foreach ($this->children as $child)
			{
				if ($child->tag_name == 'input' && $child['type'] == 'checkbox')
				{
					if ($child['checked'])
					{
						$class_names['enabled'] = true;

						break;
					}
				}
			}
		}

		return parent::render_class($class_names);
	}
}
