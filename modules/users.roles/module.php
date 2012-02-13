<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Users\Roles;

use ICanBoogie\Operation;
use ICanBoogie\Route;
use ICanBoogie\ActiveRecord\Users\Role;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class Module extends \Icybee\Module
{
	const OPERATION_PERMISSIONS = 'permissions';

	public static $levels = array
	(
		self::PERMISSION_NONE => 'none',
		self::PERMISSION_ACCESS => 'access',
		self::PERMISSION_CREATE => 'create',
		self::PERMISSION_MAINTAIN => 'maintain',
		self::PERMISSION_MANAGE => 'manage',
		self::PERMISSION_ADMINISTER => 'administer'
	);

	/**
	 * Overrides the methods to create the "Visitor" and "User" roles.
	 *
	 * @see ICanBoogie.Module::install()
	 */
	public function install(\ICanBoogie\Errors $errors)
	{
		$rc = parent::install($errors);

		if (!$rc)
		{
			return $rc;
		}

		$model = $this->model;

		try
		{
			$this->model[1];
		}
		catch (\ICanBoogie\Exception\MissingRecord $e)
		{
			$role = Role::from
			(
				array
				(
					Role::NAME => t('Visitor')
				),

				array($model)
			);

			var_dump($role);

			$role->save();
		}

		try
		{
			$this->model[2];
		}
		catch (\ICanBoogie\Exception\MissingRecord $e)
		{
			$role = Role::from
			(
				array
				(
					Role::NAME => t('User')
				),

				array($model)
			);

			$role->save();
		}

		return $rc;
	}

	public function is_installed(\ICanBoogie\Errors $errors)
	{
		try
		{
			$this->model->find(array(1, 2));
		}
		catch (\ICanBoogie\Exception\MissingRecord $e)
		{
			var_dump($e);

			if (!$e->rc[1])
			{
				$errors[$this->id] = t('Visitor role is missing');
			}

			if (!$e->rc[2])
			{
				$errors[$this->id] = t('User role is missing');
			}
		}
	}

	protected function block_edit($properties, $permission)
	{
		return array
		(
			Element::CHILDREN => array
			(
				Role::NAME => new Text
				(
					array
					(
						Form::LABEL => '.title',
						Element::REQUIRED => true
					)
				)
			)
		);
	}

	protected function block_manage()
	{
		global $core, $document;

		$document->css->add(\Icybee\ASSETS . 'css/manage.css', -170);
		$document->css->add('public/admin.css');

		$packages = array();
		$modules = array();

		foreach ($core->modules->descriptors as $m_id => $descriptor)
		{
			if (!isset($core->modules[$m_id]))
			{
				continue;
			}

			$name = isset($descriptor[self::T_TITLE]) ? $descriptor[self::T_TITLE] : $m_id;

			if (isset($descriptor[self::T_PERMISSION]))
			{
				if ($descriptor[self::T_PERMISSION] != self::PERMISSION_NONE)
				{
					$name .= ' <em>(';
					$name .= self::$levels[$descriptor[self::T_PERMISSION]];
					$name .= ')</em>';
				}
				else if (empty($descriptor[self::T_PERMISSIONS]))
				{
					continue;
				}
			}

			if (isset($descriptor[self::T_CATEGORY]))
			{
				$package = $descriptor[self::T_CATEGORY];
			}
			else
			{
				list($package) = explode('.', $m_id);
			}

			$package = t($package, array(), array('scope' => array('module_category', 'title'), 'default' => $package));

			$packages[$package][t($name)] = array_merge
			(
				$descriptor, array
				(
					self::T_ID => $m_id
				)
			);
		}

		uksort($packages, 'wd_unaccent_compare_ci');

		$packages = array_merge
		(
			array
			(
				t('General') => array
				(
					t('All') => array(self::T_ID => 'all')
				)
			),

			$packages
		);

		#
		# load roles
		#

		$roles = $this->model->all;

		//
		// create manager
		//

		$rc = '';

		$rc .= '<form name="roles" action="" method="post" enctype="multipart/form-data">';
		$rc .= '<input type="hidden" name="' . Operation::DESTINATION . '" value="' . $this . '" />';

		// table

		$rc .= '<table class="manage group" cellpadding="4" cellspacing="0">';

		//
		// table header
		//

		$span = 1;
		$context = $core->site->path;

		$rc .= '<thead>';
		$rc .= '<tr>';
		$rc .= '<th>&nbsp;</th>';

		foreach ($roles as $role)
		{
			$span++;

			$rc .= '<th><div>';

			if ($role->rid == 0)
			{
				$rc .= $role->title;
			}
			else
			{
				$rc .= new Element
				(
					'a', array
					(
						Element::INNER_HTML => $role->name,
						'href' => $context . '/admin/' . $this . '/' . $role->rid . '/edit',
						'title' => t('Edit entry')
					)
				);
			}

			$rc .= '</div></th>';
		}

		$rc .= '</tr>';
		$rc .= '</thead>';

		if (1)
		{
			$actions_rows = '';

			foreach ($roles as $role)
			{
				$actions_rows .= '<td>';

				if ($role->rid == 1 || $role->rid == 2)
				{
					$actions_rows .= '&nbsp;';
				}
				else
				{
					$actions_rows .= '<a class="button danger small" href="' . $context . '/admin/users.roles/' . $role->rid . '/delete">Supprimer</a>';
				}

				$actions_rows .= '</td>';
			}

			$rc .= <<<EOT
<tfoot>
	<tr class="footer">
		<td>
		<div class="jobs">
			<a class="operation-delete" href="#" rel="op-delete">Delete the selected entries</a>
		</div>
		</td>

		$actions_rows

	</tr>
</tfoot>
EOT;
		}

		$rc .= '<tbody>';

		//
		//
		//


		$role_options = array();

		foreach (self::$levels as $i => $level)
		{
			$role_options[$i] = t('permission.' . $level, array(), array('default' => $level));
		}


		$user_has_access = $core->user->has_permission(self::PERMISSION_ADMINISTER, $this);

		foreach ($packages as $p_name => $modules)
		{
			$rc .= '<tr class="module">';
			$rc .= '<td colspan="' . $span . '">';
			$rc .= $p_name;
			$rc .= '</td>';
			$rc .= '</tr>';

			$n = 0;

			//
			// admins
			//

			uksort($modules, 'wd_unaccent_compare_ci');

			foreach ($modules as $m_name => $m_desc)
			{
				$m_id = $m_desc[self::T_ID];
				$flat_id = strtr($m_id, '.', '_');


				$rc .= '<tr class="admin">';

				$rc .= '<td>';
				$rc .= Route::find('/admin/' . $m_id) ? '<a href="' . $context . '/admin/' . $m_id . '">' . $m_name . '</a>' : $m_name;
				$rc .= '</td>';

				foreach ($roles as $role)
				{
					$rc .= '<td>';

					if (isset($m_desc[self::T_PERMISSION]))
					{
						if ($m_desc[self::T_PERMISSION] != self::PERMISSION_NONE)
						{
							$level = $m_desc[self::T_PERMISSION];

							$rc .= new Element
							(
								Element::TYPE_CHECKBOX, array
								(
									'name' => 'roles[' . $role->rid . '][' . $m_id . ']',
									'checked' => isset($role->levels[$m_id]) && ($role->levels[$m_id] = $level)
								)
							);
						}
						else
						{
							$rc .= '&nbsp;';
						}
					}
					else
					{
						if ($user_has_access)
						{
							$options = $role_options;

							if ($m_id != 'all')
							{
								$options = array('inherit' => '') + $options;
							}

							$rc .= new Element
							(
								'select', array
								(
									Element::OPTIONS => $options,

									'name' => 'roles[' . $role->rid . '][' . $m_id . ']',
									'value' => isset($role->perms[$m_id]) ? $role->perms[$m_id] : null
								)
							);
						}
						else
						{
							$level = isset($role->perms[$m_id]) ? $role->perms[$m_id] : null;

							if ($level)
							{
								$rc .= self::$levels[$level];
							}
							else
							{
								$rc .= '&nbsp;';
							}
						}
					}

					$rc .= '</td>';
				}

				$rc .= '</tr>';

				#
				# Permissions
				#
				# e.g. "modify own profile"
				#

				if (empty($m_desc[self::T_PERMISSIONS]))
				{
					continue;
				}

				$perms = $m_desc[self::T_PERMISSIONS];

				foreach ($perms as $pname)
				{
					$columns = '';

					foreach ($roles as $role)
					{
						$columns .= '<td>' . new Element
						(
							Element::TYPE_CHECKBOX, array
							(
								'name' => $user_has_access ? 'roles[' . $role->rid . '][' . $pname . ']' : NULL,
								'checked' => $role->has_permission($pname)
							)
						)
						. '</td>';
					}

					$label = t($pname, array(), array('scope' => array($flat_id, 'permission')));

					$rc .= <<<EOT
<tr class="perm">
	<td><span title="$pname">$label</span></td>
	$columns
</tr>
EOT;
				}
			}
		}

		$rc .= '</tbody>';
		$rc .= '</table>';

		//
		// submit
		//

		if ($user_has_access)
		{
			$rc .= '<div class="group">';

			$rc .= new Button
			(
				'Save permissions', array
				(
					'class' => 'save',
					'type' => 'submit',
					'value' => self::OPERATION_PERMISSIONS
				)
			);

			$rc .= '</div>';
		}

		$rc .= '</form>';

		return $rc;
	}
}