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

use ICanBoogie\Module;
use ICanBoogie\Operation;

use Brickrouge\A;
use Brickrouge\Element;
use Brickrouge\DropdownMenu;

/**
 * The _user menu_ element is made of two parts: a link to the user profile and a dropdown menu.
 * The dropdown menu provides a link to the user profile and a link to logout the user.
 *
 * @property-read string $path
 * @property-read \Icybee\Modules\Users\User $user
 */
class UserMenu extends Element
{
	protected function get_path()
	{
		return $this->app->request->path;
	}

	protected function get_user()
	{
		return $this->app->user;
	}

	public function __construct(array $attributes = [])
	{
		parent::__construct('div', $attributes);
	}

	protected function render_inner_html()
	{
		$user = $this->user;

		$username = new A($user->name, \ICanBoogie\Routing\contextualize('/admin/profile'));

		$options = [

			\ICanBoogie\Routing\contextualize('/admin/profile') => 'Profile',
			false,
			Operation::encode('users/logout') => 'Logout'

		];

		array_walk($options, function(&$v, $k) {

			if (!is_string($v))
			{
				return;
			}

			$v = new A($v, $k);

		});

		$menu = new DropdownMenu([

			DropdownMenu::OPTIONS => $options,

			'value' => $this->path

		]);

		return <<<EOT
$username
<span class="btn-group">
	<span class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user icon-white"></i> <span class="caret"></span></span>
	$menu
</span>
EOT;
	}
}
