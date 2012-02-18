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
	public function __construct(array $attributes=array())
	{
		parent::__construct($attributes);

		$this->tag_name = 'div';
		$this->add_class('group');
	}

	protected function render_group_legend($legend)
	{
		return '<div class="group-legend">' . $legend . '</div>';
	}
}