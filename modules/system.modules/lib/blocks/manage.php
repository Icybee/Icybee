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

use Brickrouge\Button;

use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\Element;
use Brickrouge\Form;

class ManageBlock extends Form
{
	protected $module;

	public function __construct(Module $module, array $attributes=array())
	{
		global $core;

		$this->module = $module;

		$is_installer_mode = isset($attributes[Module::MANAGE_MODE]) && $attributes[Module::MANAGE_MODE] == Module::MANAGE_MODE_INSTALLER;

		if (!$core->user->has_permission(Module::PERMISSION_ADMINISTER, $module))
		{
			throw new HTTPException("You don't have permission to administer modules.", array(), 403);
		}

		parent::__construct
		(
			$attributes + array
			(
				self::ACTIONS => new Button
				(
					'Disable selected module', array
					(
						'class' => 'btn-primary btn-danger',
						'type' => 'submit'
					)
				),

				'class' => 'form-primary block--modules-manage'
			)
		);

		if (!$is_installer_mode)
		{
			$this->hiddens[Operation::NAME] = Module::OPERATION_DEACTIVATE;
			$this->hiddens[Operation::DESTINATION] = $module;
		}
	}

	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add(\Icybee\ASSETS . 'css/manage.css');
		$document->css->add('manage.css');
	}

	protected function render_inner_html()
	{
		global $core;

		$is_installer_mode = isset($options[Module::MANAGE_MODE]) && $options[Module::MANAGE_MODE] == Module::MANAGE_MODE_INSTALLER;

		#
		# read and sort packages and modules
		#

		$packages = array();
		$modules = array();

		$descriptors = $core->modules->enabled_modules_descriptors;
		self::sort_descriptors($descriptors);

		foreach ($descriptors as $id => $descriptor)
		{
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

				$sub .= '<tr>';

				$sub .= '<td class="count">';

				#
				# selector
				#

				$sub .= new Element
				(
					Element::TYPE_CHECKBOX, array
					(
						'name' => Operation::KEY . '[' . $m_id . ']',
						'disabled' => $descriptor[Module::T_REQUIRED]
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

						while (isset($descriptor[Module::T_EXTENDS]))
						{
							$extends = $descriptor[Module::T_EXTENDS];

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
								$sub .= $context . '/admin/' . $this->module . '/' . $module . '/install';

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
				$rows .= $this->render_category_row($p_name, $span) . $sub;
			}
		}

		$thead = $this->render_head();

		return <<<EOT
<table class="manage" cellpadding="4" cellspacing="0">
	$thead

	<tbody>
		$rows
	</tbody>
</table>
EOT
		. parent::render_inner_html();
	}

	protected function render_head()
	{
		$label_author = t('Author');
		$label_description = t('Description');
		$label_installed = t('Installed');

		return <<<EOT
<thead>
	<tr>
	<th colspan="2"><div>&nbsp;</div></th>
	<th><div>$label_author</div></th>
	<th><div>$label_description</div></th>
	<th><div>$label_installed</div></th>
	</tr>
</thead>
EOT;
	}

	protected function render_category_row($category, $span)
	{
		return <<<EOT
<tr class="row--category">
	<td colspan="$span">$category</td>
</tr>
EOT;
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

	static protected function sort_descriptors(array &$descriptors)
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