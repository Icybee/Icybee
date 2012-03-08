<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class adjustimage_WdEditorElement extends adjustnode_WdEditorElement
{
	public function __construct(array $attributes=array())
	{
		parent::__construct
		(
			$attributes + array
			(
				self::T_CONFIG => array
				(
					'scope' => 'images'
				)
			)
		);
	}
}