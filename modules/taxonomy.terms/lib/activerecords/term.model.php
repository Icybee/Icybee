<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Taxonomy\Terms;

class Model extends \ICanBoogie\ActiveRecord\Model
{
	/**
	 * If the `termslug` property is empty it is created from the `term` property, otherwise
	 * the it is normalized.
	 */
	public function save(array $properties, $key=null, array $options=array())
	{
		if (isset($properties[Term::TERM]) && empty($properties[Term::TERMSLUG]))
		{
			$properties[Term::TERMSLUG] = \Icybee\slugize($properties[Term::TERM]);
		}
		else if (isset($properties[Term::TERMSLUG]))
		{
			$properties[Term::TERMSLUG] = \ICanBoogie\normalize($properties[Term::TERMSLUG]);
		}

		return parent::save($properties, $key, $options);
	}
}