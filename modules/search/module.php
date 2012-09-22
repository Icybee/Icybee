<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Search;

class Module extends \Icybee\Module
{
	protected function get_views()
	{
		return array
		(
			'home' => array
			(
				'title' => 'Rechercher sur le site',
				'renders' => \Icybee\Modules\Views\View::RENDERS_MANY
			)
		);
	}
}