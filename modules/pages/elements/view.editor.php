<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\Exception\HTTP as HTTPException;
use ICanBoogie\I18n;
use ICanBoogie\Module;
use BrickRouge\Element;

class view_WdEditorElement extends WdEditorElement
{
	static protected $views = array();

	static public function __static_construct()
	{
		global $core;

		self::$views = $core->configs->synthesize('views', array(__CLASS__, '__static_construct_callback'));
	}

	static public function __static_construct_callback($configs)
	{
		global $core;

		$modules_paths = array();

		foreach ($core->modules->descriptors as $descriptor)
		{
			$modules_paths[$descriptor['path']] = $descriptor;
		}

		$views = array();

		//wd_log('callback configs: \1', array($configs));

		foreach ($configs as $root => $definitions)
		{
			$module_id = null;

			if (isset($modules_paths[$root]))
			{
				$module_id = $modules_paths[$root][Module::T_ID];
			}

			foreach ($definitions as $id => $definition)
			{
				if ($module_id && empty($view['module']))
				{
					$definition['module'] = $module_id;
				}

				$local_module_id = isset($definition['module']) ? $definition['module'] : $module_id;

				#
				# view short identifiers are expanded by adding the module id.
				#

				if ($id{0} == '/')
				{
					if (!$local_module_id)
					{
						throw new Exception('Missing module id to expand view short identifier');
					}

					$id = $local_module_id . $id;
				}


				$definition['root'] = $root;

				if (empty($definition['file']) && empty($definition['block']))
				{
					list($name, $type) = explode('/', $id) + array(1 => null);

					$definition['file'] = ($type ? $type : $name);// . '.html';
				}

				if (isset($definition['block']) && empty($definition['module']))
				{
					$definition['module'] = $local_module_id;
				}

				if ($local_module_id && empty($definition['scope']))
				{
					$definition['scope'] = strtr($local_module_id, '.', '_');
				}

				if (isset($definition['file']) && $definition['file'][0] != '/')
				{
					$file = $root . '/views/' . $definition['file'];

					if (!file_exists($file))
					{
						$file = file_exists($file . '.php') ? $file . '.php' : $file . '.html';
					}

					$definition['file'] = $file;
				}

				$views[$id] = $definition;
			}
		}

		return $views;
	}

	static public function to_content(array $params, $content_id, $page_id)
	{
		global $core;

		$content = parent::to_content($params, $content_id, $page_id);

		if ($content && strpos($content, '/') !== false)
		{
			$view_target_key = 'views.targets.' . strtr($content, '.', '_');

			$core->site->metas[$view_target_key] = $page_id;
		}

		return $content;
	}

	static public function render($id)
	{
		global $core, $page;

		$patron = WdPatron::get_singleton();

		if (empty($page))
		{
			$page = $core->site->resolve_view_target($id);

			if (!$page)
			{
				$page = $core->site->home;
			}
		}

		if (empty(self::$views[$id]))
		{
			throw new Exception('Unknown view: %id', array('%id' => $id));
		}

		$view = new ICanBoogie\View($id, self::$views[$id], $patron, $core->document, $page);

		return $view();
	}

	public function __construct($tags, $dummy=null)
	{
		parent::__construct
		(
			'div', $tags + array
			(
				'class' => 'view-editor'
			)
		);
	}

	public function render_inner_html()
	{
		global $core;

		$document = $core->document;

		$document->css->add('../public/view.css');
		$document->js->add('../public/view.js');

		$rc = parent::render_inner_html();

		$value = $this->get('value');
		$name = $this->get('name');

		$selected_category = null;
		$selected_subcategory = null;

		$by_category = array();
		$descriptors = $core->modules->descriptors;

//		var_dump(self::$views);

		foreach (self::$views as $id => $view)
		{
			list($module_id, $type) = explode('/', $id) + array(1 => null);

			if (!isset($core->modules[$module_id]))
			{
				continue;
			}

			$category = 'Misc';
			$subcategory = 'Misc';

			if ($type !== null && isset($descriptors[$module_id]))
			{
				$descriptor = $descriptors[$module_id];

				if (isset($descriptor[Module::T_CATEGORY]))
				{
					$category = $descriptors[$module_id][Module::T_CATEGORY];
					$category = t($category, array(), array('scope' => array('module_category', 'title')));
				}

				$subcategory = $descriptor[Module::T_TITLE];
			}

			$by_category[$category][$subcategory][$id] = $view;

			if ($id == $value)
			{
				$selected_category = $category;
				$selected_subcategory = $subcategory;
			}
		}

		uksort($by_category, 'wd_unaccent_compare_ci');

		$rc = '<table>';
		$rc .= '<tr>';

		$rc .= '<td class="view-editor-categories"><ul>';

		foreach ($by_category as $category => $dummy)
		{
			$rc .= '<li' . ($category == $selected_category ? ' class="active selected"' : '') . '><a href="#select">' . wd_entities($category) . '</a></li>';
		}

		$rc .= '</ul></td>';

		#
		#
		#

		$rc .= '<td class="view-editor-subcategories">';

		foreach ($by_category as $category => $subcategories)
		{
			uksort($subcategories, 'wd_unaccent_compare_ci');

			$by_category[$category] = $subcategories;

			$rc .= '<ul' . ($category == $selected_category ? ' class="active selected"' : '') . '>';

			foreach ($subcategories as $subcategory => $views)
			{
				$rc .= '<li' . ($subcategory == $selected_subcategory ? ' class="active selected"' : '') . '><a href="#select">' . wd_entities($subcategory) . '</a></li>';
			}

			$rc .= '</ul>';
		}

		$rc .= '</ul></td>';

		#
		#
		#

		$context = $core->site->path;

		$rc .= '<td class="view-editor-views">';

		foreach ($by_category as $category => $subcategories)
		{
			foreach ($subcategories as $subcategory => $views)
			{
				$active = '';
				$items = array();

				foreach ($views as $id => $view)
				{
					if (empty($view['title']))
					{
						continue;
					}

					$title = $view['title'];
					$description = null;

					if (isset($view['description']))
					{
						$description = $view['description'];

						// FIXME-20101008: finish that ! it this usefull anyway ?

						$description = strtr
						(
							$description, array
							(
								'#{url}' => $context . '/admin/'
							)
						);
					}

					if ($id == $value)
					{
						$active = ' class="active"';
					}

					$items[$title] = new Element
					(
						Element::E_RADIO, array
						(
							Element::T_LABEL => $title,
							Element::T_DESCRIPTION => $description,

							'name' => $name,
							'value' => $id,
							'checked' => ($id == $value)
						)
					);
				}

				uksort($items, 'wd_unaccent_compare_ci');

				$rc .= "<ul$active><li>" . implode('</li><li>', $items) . '</li></ul>';
			}


		}

		$rc .= '</td>';

		$rc .= '</tr>';
		$rc .= '</table>';

		return $rc;
	}
}