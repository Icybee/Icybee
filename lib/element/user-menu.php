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
use ICanBoogie\Route;

use Brickrouge\A;
use Brickrouge\Element;
use Brickrouge\DropdownMenu;

use Icybee\Modules\Users\Roles\Role;

/**
 * The _user menu_ element is made of two parts: a link to the user profile and a dropdown menu.
 * The dropdown menu provides a link to the user profile and a link to logout the user.
 */
class UserMenu extends Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct('div', $attributes);
	}

	protected function render_inner_html()
	{
		global $core;

		$user = $core->user;
		$site = $core->site;

		$roles = '';

		if ($user->is_admin)
		{
			$roles = 'Admin';
		}
		else if ($user->has_permission(Module::PERMISSION_ADMINISTER, 'users.roles'))
		{
			foreach ($user->roles as $role)
			{
				$roles .= ', <a href="' . $site->path . '/admin/users.roles/' . $role->rid . '/edit">' . $role->name . '</a>';
			}

			$roles = substr($roles, 2);
		}
		else
		{
			$n = count($user->roles);

			foreach ($user->roles as $role)
			{
				if ($n > 1 && $role->rid == Role::USER_RID)
				{
					continue;
				}

				$roles .= ', ' . $role->name;
			}

			$roles = substr($roles, 2);
		}

		$username = new A($user->name, \ICanBoogie\Routing\contextualize('/admin/profile'));

		$options = array
		(
			\ICanBoogie\Routing\contextualize('/admin/profile') => 'Profile',
			false,
			Operation::encode('users/logout') => 'Logout'
		);

		array_walk
		(
			$options, function(&$v, $k)
			{
				if (!is_string($v))
				{
					return;
				}

				$v = new A($v, $k);
			}
		);

		$menu = new DropdownMenu
		(
			array
			(
				DropdownMenu::OPTIONS => $options,

				'value' => $core->request->path
			)
		);

		return <<<EOT
$username
<span class="btn-group">
	<span class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user icon-white"></i> <span class="caret"></span></span>
	$menu
</span>
EOT;
	}
}