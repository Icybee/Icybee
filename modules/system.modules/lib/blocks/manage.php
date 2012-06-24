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

use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;

class ManageBlock extends Form
{
	protected $module;

	public function __construct(Module $module, array $attributes=array())
	{
		global $core;

		$this->module = $module;

		if (!$core->user->has_permission(Module::PERMISSION_ADMINISTER, $module))
		{
			throw new HTTPException("You don't have permission to administer modules.", array(), 403);
		}

		parent::__construct
		(
			$attributes + array
			(
				'class' => 'form-primary block--modules-manage'
			)
		);

		$this->attach_buttons();

		$this->hiddens[Operation::DESTINATION] = $module;
		$this->hiddens[Operation::NAME] = Module::OPERATION_DEACTIVATE;
	}

	protected function get_columns()
	{
		return array
		(
			'key' => array
			(
				'label' => null
			),

			'title' => array
			(
				'label' => 'Module'
			),

			'author' => array
			(
				'label' => 'Author'
			),

			'description' => array
			(
				'label' => 'Description'
			),

			'dependency' => array
			(
				'label' => 'Dependency'
			),

			'install' => array
			(
				'label' => 'Installed'
			)
		);
	}

	protected function get_descriptors()
	{
		global $core;

		return $core->modules->enabled_modules_descriptors;
	}

	protected function get_categories()
	{
		$categories = array();
		$modules = array();

		$descriptors = $this->descriptors;
		self::sort_descriptors($descriptors);

		foreach ($descriptors as $id => $descriptor)
		{
			$category = $descriptor[Module::T_CATEGORY];

			if (!$category)
			{
				list($category) = explode('.', $id);
			}

			$category = t($category, array(), array('scope' => 'module_category', 'default' => ucfirst($category)));
			$categories[$category][$id] = $descriptor;
		}

		uksort($categories, 'ICanBoogie\unaccent_compare_ci');

		return $categories;
	}

	protected static function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add(\Icybee\ASSETS . 'css/manage.css');
		$document->css->add('admin.css');
	}

	protected function render_inner_html()
	{
		global $core;

		#
		# read and sort packages and modules
		#

		$categories = $this->categories;
		$columns = $this->columns;

		$body = '';
		$span = count($columns);

		foreach ($categories as $category => $descriptors)
		{
			$sub = null;

			foreach ($descriptors as $module_id => $descriptor)
			{
				$sub .= '<tr>';
				$sub .= $this->render_body_row($columns, $descriptor, $module_id);
				$sub .= '</tr>';
			}

			if ($sub)
			{
				$body .= $this->render_category_row($category, $span) . $sub;
			}
		}

		$thead = $this->render_head($columns);

		return <<<EOT
<table class="manage" cellpadding="4" cellspacing="0">
	$thead

	<tbody>
		$body
	</tbody>
</table>
EOT
		. parent::render_inner_html();
	}

	protected function render_head(array $columns)
	{
		$html = '';

		foreach ($columns as $id => $column)
		{
			$html .= '<th><div>' . ($column['label'] ? t($column['label'], array(), array('scope' => 'title')) : '&nbsp;') . '</div></th>';
		}

		return <<<EOT
<thead>
	<tr>
	$html
	</tr>
</thead>
EOT;
	}

	protected function render_category_row($category, $span)
	{
		$span--;

		return <<<EOT
<tr class="section-title">
	<td class="cell--key">&nbsp;</td><td colspan="$span">$category</td>
</tr>
EOT;
	}

	protected function render_body_row(array $columns, array $descriptor, $module_id)
	{
		$html = '';

		foreach ($columns as $column_id => $column)
		{
			$callback = 'render_cell_' . $column_id;

			$html .= '<td class="cell--' . $column_id . '">';
			$html .= $this->$callback($descriptor, $module_id) ?: '&nbsp';
			$html .= '</td>';
		}

		return $html;
	}

	protected function render_cell_key(array $descriptor, $module_id)
	{
		global $core;

		$disabled = $descriptor[Module::T_REQUIRED];

		if ($core->modules->usage($module_id))
		{
			$disabled = true;
		}

		return new Element
		(
			Element::TYPE_CHECKBOX, array
			(
				'name' => Operation::KEY . '[' . $module_id . ']',
				'disabled' => $disabled
			)
		);
	}

	protected function render_cell_title(array $descriptor, $module_id)
	{
		$title = $descriptor['_locale_title'];

		$html = \ICanBoogie\Routes::get()->find('/admin/' . $module_id) ? '<a href="' . Route::contextualize('/admin/' . $module_id) . '">' . $title . '</a>' : $title;

		$description = t('module_description.' . strtr($module_id, '.', '_'), array(), array('default' => t($descriptor[Module::T_DESCRIPTION]) ?: '<em class="light">' . t('No description') . '</em>'));

		if ($description)
		{
			$html .= '<div class="small">' . $description . '</div>';
		}

		return $html;
	}

	protected function render_cell_author(array $descriptor, $module_id)
	{
		return 'Olivier Laviale';
	}

	protected function render_cell_description(array $descriptor, $moduleid)
	{
		global $core;

		$html  = '<span class="small lighter">v';
		$html .= $descriptor[Module::T_VERSION];
		$html .= '</span>';

		return $html;
	}

	protected function render_cell_dependency(array $descriptor, $module_id)
	{
		global $core;

		$html = '';
		$extends = $descriptor[Module::T_EXTENDS];

		if ($extends)
		{
			$label = self::resolve_module_title($extends);
			$class = isset($core->modules[$extends]) ? 'success' : 'warning';

			$html .= '<div class="extends">Extends: ';
			$html .= '<span class="label label-' . $class . '">' . $label . '</span>';
			$html .= '</div>';
		}

		$requires = $descriptor[Module::T_REQUIRES];

		if ($requires)
		{
			$html .= '<div class="requires">Requires: ';

			foreach ($requires as $require_id => $version)
			{
				$label = self::resolve_module_title($require_id);

				if (!isset($core->modules[$require_id]))
				{
					$html .= '<span class="label label-warning">' . $label . '</span>';
				}
				else
				{
					$html .= '<span class="label label-success">' . $label . '</span>';
				}

				$html .= '<span class="small light"> ' . $version . '</span> ';
			}

			$html .= '</div>';
		}

		$usage = $core->modules->usage($module_id);

		if ($usage)
		{
			$html .= '<div class="usage light">' . t('Used by :count modules', array(':count' => $usage)) . '</div>';
		}

		return $html;
	}

	protected function render_cell_install(array $descriptor, $module_id)
	{
		global $core;

		try
		{
			$module = $core->modules[$module_id];
		}
		catch (\Exception $e)
		{
			return '<div class="alert alert-error">' . $e->getMessage() . '</div>';
		}

		$html = '';
		$is_installed = false;

		# EXTENDS

		$errors = new \ICanBoogie\Errors;
		$extends_errors = new \ICanBoogie\Errors;
		$n_errors = count($errors);

		while ($descriptor[Module::T_EXTENDS])
		{
			$extends = $descriptor[Module::T_EXTENDS];

			if (empty($core->modules->descriptors[$extends]))
			{
				$errors[$module_id] = t('Requires the %module module which is missing.', array('%module' => $extends));

				break;
			}
			else if (!isset($core->modules[$extends]))
			{
				$errors[$module_id] = t('Requires the %module module which is disabled.', array('%module' => $extends));

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
					$errors[$module_id] = t('Requires the %module module which is disabled.', array('%module' => $extends));

					break;
				}
			}

			$descriptor = $core->modules->descriptors[$extends];
		}

		if ($n_errors != count($errors))
		{
			$html .= '<div class="alert alert-error">' . implode('<br />', (array) $errors[$module_id]) . '</div>';
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
				$errors[$module->id] = t
				(
					'Exception with module %module: :message', array
					(
						'%module' => (string) $module,
						':message' => $e->getMessage()
					)
				);
			}

			if ($is_installed)
			{
				$html .= t('Installed');
			}
			else if ($is_installed === false)
			{
				$html .= '<a class="install" href="';
				$html .= Route::contextualize("/admin/{$this->module}/{$module}/install");

				\ICanBoogie\log_error('The module %title is not properly installed.', array('title' => $module->title));

				$html .= '">' . t('Install module') . '</a>';

				if (isset($errors[$module_id]))
				{
					$html .= '<div class="error">' . implode('; ', (array) $errors[$module_id]) . '</div>';
				}
			}
			else // null
			{
				$html .= '<em class="not-applicable light">Not applicable</em>';
			}
		}

		return $html;
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
						'scope' => 'module_title',
						'default' => isset($descriptor[Module::T_TITLE]) ? $descriptor[Module::T_TITLE] : $id
					)
				);

				$descriptor['_locale_title'] = $title;

				return \ICanBoogie\remove_accents($title);
			}
		);
	}

	protected function attach_buttons()
	{
		\ICanBoogie\Events::attach
		(
			'Icybee\Admin\Element\ActionbarToolbar::alter_buttons', function(\ICanBoogie\Event $event, \Icybee\Admin\Element\ActionbarToolbar $target)
			{
				$event->buttons[] = new Button
				(
					'Disable selected modules', array
					(
						'class' => 'btn-primary btn-danger',
						'type' => 'submit',
						'data-target' => '.form-primary'
					)
				);
			}
		);
	}

	public static function resolve_module_title($module_id)
	{
		global $core;

		return t
		(
			'module_title.' . strtr($module_id, '.', '_'), array(), array
			(
				'default' => isset($core->modules->descriptors[$module_id]) ? $core->modules->descriptors[$module_id][Module::T_TITLE] : $module_id
			)
		);
	}
}