<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\System\Modules;

use ICanBoogie\Exception\HTTP as HTTPException;
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\Element;
use Brickrouge\Form;

use Icybee;

class Module extends \Icybee\Module
{
	const MANAGE_MODE = '#manage-mode';
	const MANAGE_MODE_INSTALLER = 'installer';

	const OPERATION_ACTIVATE = 'activate';
	const OPERATION_DEACTIVATE = 'deactivate';

	protected function block_manage(array $options=array())
	{
		global $core;

		$is_installer_mode = isset($options[self::MANAGE_MODE])	&& $options[self::MANAGE_MODE] == self::MANAGE_MODE_INSTALLER;

		if (!$core->user->has_permission(self::PERMISSION_ADMINISTER, $this))
		{
			throw new HTTPException("You don't have permission to administer modules.", array(), 403);
		}

		$form = $this->form_manage($options);

		if (!$is_installer_mode)
		{
			$form->hiddens[Operation::NAME] = self::OPERATION_DEACTIVATE;
			$form->hiddens[Operation::DESTINATION] = $this;
		}

		return $form;
	}

	protected function form_manage(array $options=array())
	{
		global $core, $document;

		$document->css->add(Icybee\ASSETS . 'css/manage.css');
		$document->css->add('public/manage.css', 10);

		$is_installer_mode = isset($options[self::MANAGE_MODE]) && $options[self::MANAGE_MODE] == self::MANAGE_MODE_INSTALLER;

		#
		# read and sort packages and modules
		#

		$packages = array();
		$modules = array();

		$descriptors = $core->modules->enabled_modules_descriptors;
		self::sort_descriptors($descriptors);

		foreach ($descriptors as $id => $descriptor)
		{
			if (isset($descriptor[self::T_CATEGORY]))
			{
				$category = $descriptor[self::T_CATEGORY];
			}
			else
			{
				list($category) = explode('.', $id);
			}

			$category = t($category, array(), array('scope' => 'module_category.title', 'default' => ucfirst($category)));
			$title = $descriptor['_locale_title'];

			$packages[$category][$id] = $descriptor;
		}

		uksort($packages, 'wd_unaccent_compare_ci');

		$categories = $packages;
		$rows = '';

		$span = $is_installer_mode ? 4 : 5;
		$context = $core->site->path;
		$errors = new \ICanBoogie\Errors;
		$extends_errors = new \ICanBoogie\Errors;

		foreach ($packages as $p_name => $descriptors)
		{
			$sub = null;
			$i = 0;

			foreach ($descriptors as $m_id => $descriptor)
			{
				$title = $descriptor['_locale_title'];

				#
				#
				#

				if ($i++ % 2)
				{
					$sub .= '<tr class="even">';
				}
				else
				{
					$sub .= '<tr>';
				}

				$sub .= '<td class="count">';

				#
				# selector
				#

				$sub .= new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						'name' => Operation::KEY . '[' . $m_id . ']',
						'disabled' => $descriptor[self::T_REQUIRED]
					)
				);

				$sub .= '</td>';

				$sub .= '<td class="name">';
				$sub .= Route::find('/admin/' . $m_id) ? '<a href="' . $context . '/admin/' . $m_id . '">' . $title . '</a>' : $title;
				$sub .= '</td>';

				#
				# Author
				#

				$sub .= '<td>';
				$sub .= 'Olivier Laviale';
				$sub .= '</td>';

				#
				# Description
				#

				$description = $this->render_cell_description($descriptor, $m_id);

				$sub .= '<td>';
				$sub .= $description ? $description : '&nbsp;';
				$sub .= '</td>';

				if (!$is_installer_mode)
				{
					#
					# because disabled module cannot be loaded, we need to trick the system
					#

					if (isset($core->modules[$m_id]))
					{
						try
						{
							$module = $core->modules[$m_id];
						}
						catch (\Exception $e)
						{
							$sub .= '<td class="warn">' . $e->getMessage() . '</td>';

							continue;
						}

						$is_installed = false;


						# EXTENDS

						$d = $descriptor;
						$n_errors = count($errors);

						while (isset($descriptor[self::T_EXTENDS]))
						{
							$extends = $descriptor[self::T_EXTENDS];

							if (empty($core->modules->descriptors[$extends]))
							{
								$errors[$m_id] = t('Requires the %module module which is missing.', array('%module' => $extends));

								break;
							}
							else if (!isset($core->modules[$extends]))
							{
								$errors[$m_id] = t('Requires the %module module which is disabled.', array('%module' => $extends));

								break;
							}
							else
							{
								$extends_errors->clear();
								$extends_module = $core->modules[$extends];
								$extends_is_installed = $extends_module->is_installed($extends_errors);

								if (count($extends_errors))
								{
									$extends_is_installed = false;
								}

								if (!$extends_is_installed)
								{
									$errors[$m_id] = t('Requires the %module module which is disabled.', array('%module' => $extends));

									break;
								}
							}

							$descriptor = $core->modules->descriptors[$extends];
						}

						if ($n_errors != count($errors))
						{
							$sub .= '<td class="not-applicable">';
							$sub .= '<div class="error">' . implode('<br />', (array) $errors[$m_id]) . '</div>';
							$sub .= '</td>';
						}
						else
						{
							try
							{
								$n_errors = count($errors);
								$is_installed = $module->is_installed($errors);

								if (count($errors) != $n_errors)
								{
									$is_installed = false;
								}
							}
							catch (\Exception $e)
							{
								$errors[$module->id] = t('Exception with module %module: :message', array('%module' => (string) $module, ':message' => $e->getMessage()));
							}

							if ($is_installed)
							{
								$sub .= '<td class="installed">' . t('Installed') . '</td>';
							}
							else if ($is_installed === false)
							{
								$sub .= '<td>';
								/*
								$sub .= t('Not installed');
								$sub .= ' ';
								*/
								$sub .= '<a class="install" href="';
								$sub .= $context . '/admin/' . $this . '/' . $module . '/install';

								$sub .= '">' . t('Install module') . '</a>';

								if (isset($errors[$m_id]))
								{
									$sub .= '<div class="error">' . implode('; ', (array) $errors[$m_id]) . '</div>';
								}

								$sub .= '</td>';
							}
							else // null
							{
								$sub .= '<td class="not-applicable">';
								$sub .= 'Not applicable';
								$sub .= '</td>';
							}
						}
					}
					else
					{
						$sub .= '<td class="not-applicable">';
						$sub .= 'Module is disabled';
						$sub .= '</td>';
					}
				}

				$sub .= '</tr>';
			}

			if ($sub)
			{
				$rows .= <<<EOT
<tr class="module">
	<td colspan="$span">$p_name</td>
</tr>

$sub
EOT;
			}
		}

		$label_author = t('Author');
		$label_description = t('Description');
		$label_button = t('Désactiver les modules sélectionnés');

		$th_installed = null;

		if (!$is_installer_mode)
		{
			$th_installed = '<th><div>' . t('Installed') . '</div></th>';
		}

// 		$alert_message = new \Brickrouge\AlertMessage($errors);

		$contents  = <<<EOT
<table class="manage" cellpadding="4" cellspacing="0">
	<thead>
		<tr>
		<th colspan="2"><div>&nbsp;</div></th>
		<th><div>$label_author</div></th>
		<th><div>$label_description</div></th>
		$th_installed
		</tr>
	</thead>

	<tfoot>
		<tr>
		<td colspan="5"><button type="submit" class="btn-danger">$label_button</button></td>
		</tr>
	</tfoot>

	<tbody>
		$rows
	</tbody>
</table>
EOT;

		return new Form
		(
			array
			(
				Element::CHILDREN => array
				(
					$contents
				),

				'class' => 'management'
			),

			'div'
		);
	}

	protected function block_install($module_id)
	{
		global $core;

		if (!$core->user->has_permission(self::PERMISSION_ADMINISTER, $this))
		{
			return '<div class="alert alert-error">' . t('You don\'t have enought privileges to install packages.') . '</div>';
		}

		if (empty($core->modules[$module_id]))
		{
			return '<div class="alert alert-error">' . t('The module %module_id does not exists.', array('%module_id' => $module_id)) . '</div>';
		}

		$errors = new \ICanBoogie\Errors;
		$module = $core->modules[$module_id];

		$is_installed = $module->is_installed($errors);

		if ($is_installed && !count($errors))
		{
			return '<div class="alert alert-error">' . t('The module %module is already installed', array('%module' => $module_id)) . '</div>';
		}

		$errors->clear();
		$is_installed = $module->install($errors);

		if (!$is_installed || count($errors))
		{
			return '<div class="alert alert-error">' . t('Unable to install the module %module', array('%module' => $module_id)) . '</div>';
		}

		return '<div class="alert alert-success">' . t('The module %module has been installed. <a href="' . $core->site->path . '/admin/' . $this . '">Retourner à la liste.</a>', array('%module' => $module_id)) . '</div>';
	}

	protected function block_inactives()
	{
		global $core, $document;

		$document->css->add(Icybee\ASSETS . 'css/manage.css');
		$document->css->add('public/manage.css', 10);

		#
		# read and sort packages and modules
		#

		$categories = array();
		$modules = array();

		$descriptors = $core->modules->disabled_modules_descriptors;

		self::sort_descriptors($descriptors);

		foreach ($descriptors as $id => $descriptor)
		{
			if ($descriptor[Module::T_REQUIRED])
			{
				unset($descriptors[$id]);

				continue;
			}

			$name = $descriptor[Module::T_TITLE];

			if (isset($descriptor[Module::T_CATEGORY]))
			{
				$category = $descriptor[Module::T_CATEGORY];
			}
			else
			{
				list($category) = explode('.', $id);
			}

			$category = t($category, array(), array('scope' => 'module_category.title', 'default' => ucfirst($category)));
			$title = $descriptor['_locale_title'];

			$categories[$category][$title] = array_merge
			(
				$descriptor, array
				(
					self::T_ID => $id
				)
			);
		}

		uksort($categories, 'wd_unaccent_compare_ci');

		#
		# disabled modules
		#

		$rows = '';

		foreach ($categories as $category => $descriptors)
		{
			$category_rows = null;

			foreach ($descriptors as $title => $descriptor)
			{
				$moduleid = $descriptor[Module::T_ID];

				$checkbox = new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						'name' => Operation::KEY . '[' . $moduleid . ']'
					)
				);

				$author = 'Olivier Laviale';
				$description = $this->render_cell_description($descriptor, $moduleid);

				if (!$description)
				{
					$description = '&nbsp;';
				}

				$category_rows .= <<<EOT
<tr>
	<td class="count">$checkbox</td>
	<td class="name">$title</td>
	<td>$author</td>
	<td>$description</td>
</tr>
EOT;
			}

			if ($category_rows)
			{
				$rows .= <<<EOT
<tr class="module">
	<td colspan="5">$category</td>
</tr>

$category_rows
EOT;
			}
		}

		$disabled_table = null;

		$rc = '';

		if ($rows)
		{
			$label_author = t('Author');
			$label_description = t('Description');
			$label_button = t('Activer les modules sélectionnés');

			$rc = <<<EOT
<table class="manage resume" cellpadding="4" cellspacing="0">
	<thead>
		<tr>
		<th colspan="2"><div>&nbsp;</div></th>
		<th><div>$label_author</div></th>
		<th><div>$label_description</div></th>
		</tr>
	</thead>

	<tbody>$rows</tbody>

	<tfoot>
		<tr>
		<td colspan="5"><button type="submit" class="btn-danger">$label_button</button></td>
		</tr>
	</tfoot>

</table>
EOT;
		}

		return new Form
		(
			array
			(
				Form::HIDDENS => array
				(
					Operation::NAME => self::OPERATION_ACTIVATE,
					Operation::DESTINATION => $this
				),

				Element::CHILDREN => array
				(
					$rc
				)
			)
		);
	}

	protected function render_cell_description(array $descriptor, $moduleid)
	{
		global $core;

		$rc = '';

		$description = $core->locale->translator[strtr($moduleid, '.', '_') . '.description'];

		if (!$description && isset($descriptor[Module::T_DESCRIPTION]))
		{
			$description = $descriptor[Module::T_DESCRIPTION];
		}

		if ($description)
		{
			$rc .= '<div class="description">' . $description . '</div>';
		}

		$more = '';

		if (isset($descriptor[Module::T_EXTENDS]))
		{
			$extends = $descriptor[Module::T_EXTENDS];

			$more .= '<div class="extends">Étends le module <q>' . $extends . '</q></div>';
		}

		if ($more)
		{
			$rc .= '<div class="more small">' . $more . '</div>';
		}

		return $rc;
	}

	static private function sort_descriptors(array &$descriptors)
	{
		\ICanBoogie\stable_sort
		(
			$descriptors, function(&$descriptor)
			{
				$id = $descriptor[Module::T_ID];
				$title = t
				(
					strtr($id, '.', '_'), array(), array
					(
						'scope' => 'module.title',
						'default' => isset($descriptor[Module::T_TITLE]) ? $descriptor[Module::T_TITLE] : $id
					)
				);

				$descriptor['_locale_title'] = $title;

				return wd_remove_accents($title);
			}
		);
	}
}