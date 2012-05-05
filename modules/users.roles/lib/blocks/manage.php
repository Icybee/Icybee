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
use ICanBoogie\Routes;

use Brickrouge\A;
use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;

class ManageBlock extends Form
{
	public function __construct(Module $module, array $attributes=array())
	{
		global $core;

		$this->module = $module;

		$actions = null;

		if ($core->user->has_permission(Module::PERMISSION_ADMINISTER, $module))
		{
			$actions = new Button
			(
				'Save permissions', array
				(
					'class' => 'btn-primary',
					'type' => 'submit',
					'value' => Module::OPERATION_PERMISSIONS
				)
			);
		}

		parent::__construct
		(
			$attributes + array
			(
				self::ACTIONS => $actions,
				self::HIDDENS => array
				(
					Operation::DESTINATION => $module->id,
					Operation::NAME => Module::OPERATION_PERMISSIONS
				),

				'class' => 'form-primary block-manage block--roles-manage',
				'name' => 'roles/manage'
			)
		);
	}

	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add(\Icybee\ASSETS . 'css/manage.css', -170);
		$document->css->add('manage.css');
	}

	protected function render_inner_html()
	{
		global $core;

		$packages = array();
		$modules = array();

		foreach ($core->modules->descriptors as $m_id => $descriptor)
		{
			if (!isset($core->modules[$m_id]))
			{
				continue;
			}

			$name = isset($descriptor[Module::T_TITLE]) ? $descriptor[Module::T_TITLE] : $m_id;

			if (isset($descriptor[Module::T_PERMISSION]))
			{
				if ($descriptor[Module::T_PERMISSION] != Module::PERMISSION_NONE)
				{
					$name .= ' <em>(';
					$name .= Module::$levels[$descriptor[Module::T_PERMISSION]];
					$name .= ')</em>';
				}
				else if (empty($descriptor[Module::T_PERMISSIONS]))
				{
					continue;
				}
			}

			if (isset($descriptor[Module::T_CATEGORY]))
			{
				$package = $descriptor[Module::T_CATEGORY];
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
					Module::T_ID => $m_id
				)
			);
		}

		uksort($packages, 'ICanBoogie\unaccent_compare_ci');

		$packages = array_merge
		(
			array
			(
				t('General') => array
				(
					t('All') => array(Module::T_ID => 'all')
				)
			),

			$packages
		);

		#
		# load roles
		#

		$roles = $this->module->model->all;

		//
		// create manager
		//

		$rc = '';

		// table

		$rc .= '<table class="manage" cellpadding="4" cellspacing="0">';

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
						'href' => $context . '/admin/' . $this->module . '/' . $role->rid . '/edit',
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
			$n = 0;
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
					++$n;

					$actions_rows .= new A
					(
						t('Delete', array(), array('scope' => 'button')), Route::contextualize('/admin/users.roles/' . $role->rid . '/delete'), array
						(
							'class' => 'btn btn-danger'
						)
					);
				}

				$actions_rows .= '</td>';
			}

			if ($n)
			{
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
		}

		$rc .= '<tbody>';

		//
		//
		//


		$role_options = array();

		foreach (Module::$levels as $i => $level)
		{
			$role_options[$i] = t('permission.' . $level, array(), array('default' => $level));
		}

		$user_has_access = $core->user->has_permission(Module::PERMISSION_ADMINISTER, $this->module);
		$routes = \ICanBoogie\Routes::get();

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

			uksort($modules, 'ICanBoogie\unaccent_compare_ci');

			foreach ($modules as $m_name => $m_desc)
			{
				$m_id = $m_desc[Module::T_ID];
				$flat_id = strtr($m_id, '.', '_');


				$rc .= '<tr class="admin">';

				$rc .= '<td>';
				$rc .= $routes->find('/admin/' . $m_id) ? '<a href="' . $context . '/admin/' . $m_id . '">' . $m_name . '</a>' : $m_name;
				$rc .= '</td>';

				foreach ($roles as $role)
				{
					$rc .= '<td>';

					if (isset($m_desc[Module::T_PERMISSION]))
					{
						if ($m_desc[Module::T_PERMISSION] != Module::PERMISSION_NONE)
						{
							$level = $m_desc[Module::T_PERMISSION];

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
								$rc .= Module::$levels[$level];
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

				if (empty($m_desc[Module::T_PERMISSIONS]))
				{
					continue;
				}

				$perms = $m_desc[Module::T_PERMISSIONS];

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

		return $rc . parent::render_inner_html();
	}
}