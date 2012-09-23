<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Views;

use ICanBoogie\ActiveRecord\Model;
use Icybee\Modules\Nodes\Node;
use ICanBoogie\Debug;
use ICanBoogie\Event;
use ICanBoogie\Exception;
use ICanBoogie\I18n;
use ICanBoogie\Module;
use ICanBoogie\Object;

use Brickrouge\Document;
use Brickrouge\Element;
use Brickrouge\Pager;

use BlueTihi\Context;

/**
 * A view on provided data.
 */
class View extends Object
{
	const ACCESS_CALLBACK = 'access_callback';
	const ASSETS = 'assets';
	const CLASSNAME = 'class';
	const PROVIDER = 'provider';
	const RENDERS = 'renders';
	const RENDERS_ONE = 1;
	const RENDERS_MANY = 2;
	const RENDERS_OTHER = 3;
	const TITLE = 'title';

	protected $id;

	/**
	 * The amount of data the view is rendering.
	 *
	 * - RENDERS_ONE: Renders a record.
	 *
	 * - RENDERS_MANY: Renders an array of records. A 'range' value is added to the rendering
	 * context the following properties:
	 *     - (int) limit: The maximum number of record to render.
	 *     - (int) page: The starting page.
	 *     - (int) count: The total number of records. This value is to be entered by the provider.
	 *
	 * - RENDERS_OTHER: Renders an unknown amount of data.
	 *
	 * The property is read-only.
	 *
	 * @var int
	 */
	protected $renders;
	protected $options;

	protected function volatile_get_options()
	{
		return $this->options;
	}

	protected $engine;
	protected $document;
	protected $page;
	protected $template;

	protected $module;

	protected function get_module()
	{
		global $core;

		if (isset($this->module))
		{
			return $this->module;
		}

		return $core->modules[$this->module_id];
	}

	public $module_id;
	public $type;

	public function __construct($id, array $options, $engine, $document, $page, $template=null)
	{
		unset($this->module);

		$this->options = $options;

		$this->id = $id;
		$this->type = $options['type'];
		$this->module_id = $options['module'];
		$this->renders = $options['renders'];

		$this->engine = $engine;
		$this->document = $document;
		$this->page = $page;
		$this->template = $template;
	}

	/**
	 * Renders the view.
	 *
	 * @return mixed
	 */
	public function __invoke()
	{
		$this->validate_access();

		$assets = array('css' => array(), 'js' => array());
		$options = $this->options;

		if (isset($options['assets']))
		{
			$assets = $options['assets'];
		}

		$this->add_assets($this->document, $assets);

		#

		try
		{
			$this->fire_render_before(array('id' => $this->id));

			$rc = $this->render_outer_html();

			$this->fire_render(array('id' => $this->id, 'rc' => &$rc));

			return $rc;
		}
		catch (\Brickrouge\EmptyElementException $e)
		{
			return '';
		}
	}

	/**
	 * Alters template context.
	 *
	 * @param \BlueTihi\Context $context
	 *
	 * @return \BlueTihi\Context
	 */
	protected function alter_context(Context $context)
	{
		$context['pagination'] = '';

		if (isset($context['range']['limit']) && isset($context['range']['count']))
		{
			$range = $context['range'];

			$context['pagination'] = new Pager
			(
				'div', array
				(
					Pager::T_COUNT => $range['count'],
					Pager::T_LIMIT => $range['limit'],
					Pager::T_POSITION => $range['page']
				)
			);
		}

		$context['view'] = $this;

		return $context;
	}

	/**
	 * Adds view's assets to the document.
	 *
	 * @param WdDocument $document
	 * @param array $assets
	 */
	protected function add_assets(Document $document, array $assets=array())
	{
		if (isset($assets['js']))
		{
			foreach ((array) $assets['js'] as $asset)
			{
				list($file, $priority) = (array) $asset + array(1 => 0);

				$document->js->add($file, $priority);
			}
		}

		if (isset($assets['css']))
		{
			foreach ((array) $assets['css'] as $asset)
			{
				list($file, $priority) = (array) $asset + array(1 => 0);

				$document->css->add($file, $priority);
			}
		}
	}

	/**
	 * Fires the `render:before` event on the view using the specified parameters.
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	protected function fire_render_before(array $params=array())
	{
		return Event::fire('render:before', $params, $this);
	}

	/**
	 * Fires the `render` event on the view using the specified parameters.
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	protected function fire_render(array $params=array())
	{
		return Event::fire('render', $params, $this);
	}

	/**
	 * Returns the placeholder for the empty view.
	 *
	 * @return string
	 */
	protected function render_empty_inner_html()
	{
		global $core;

		$html = null;
		$default = t('The view %name is empty.', array('%name' => $this->id));
		$type = $this->type;
		$module = $this->module;
		$module_flat_id = $module->flat_id;

		if (isset($view['on_empty']))
		{
			$html = call_user_func($view['on_empty'], $this);
		}
		else if ($module)
		{
			$placeholder = $core->site->metas[$module_flat_id . ".{$this->type}.placeholder"];

			if (!$placeholder)
			{
				$placeholder = $core->site->metas[$module_flat_id . '.placeholder'];
			}

			if ($placeholder)
			{
				$html = $placeholder;
			}
			else
			{
				$default = 'No record found.';
			}

			$default .= t
			(
				' <ul><li>The placeholder %placeholder was tried, but it does not exists.</li><li>The %message was tried, but it does not exists.</li></ul>', array
				(
					'placeholder' => $module_flat_id . ".$type.placeholder",
					'message' => $module_flat_id . '.' . $type . '.empty_view'
				)
			);
		}

		if (!$html)
		{
			$html = t('empty_view', array(), array('scope' => $module_flat_id . '.' . $type, 'default' => $default));

			if ($html)
			{
				$html = '<div class="alert">' . $html . '</div>';
			}
		}

		return $html;
	}

	/**
	 * Fires the `render_inner_html:empty` event on the view using the specified parameters.
	 *
	 * @param array $params
	 *
	 * @return mixed
	 */
	protected function fire_render_empty_inner_html(array $params=array())
	{
		return Event::fire('render_inner_html:empty', $params, $this);
	}

	protected function init_range()
	{
		global $core;

		$limit_key = $this->module->flat_id . '.limits.' . $this->type;
		$limit = $core->site->metas[$limit_key] ?: null;

		return array
		(
			'page' => empty($_GET['page']) ? 0 : (int) $_GET['page'],
			'limit' => $limit,
			'count' => null
		);
	}

	protected function provide($provider, &$context, array $conditions)
	{
		if (!class_exists($provider))
		{
			throw new \InvalidArgumentException(\ICanBoogie\format
			(
				'Provider class %class for view %id does not exists', array
				(
					'class' => $provider,
					'id' => $this->id
				)
			));
		}

		$provider = new $provider($this, $context, $this->module, $conditions, $this->renders);

		return $bind = $provider();
	}

	/**
	 * Renders the inner HTML of the view.
	 *
	 * @throws Exception
	 *
	 * @return html
	 */
	protected function render_inner_html($template_path, $engine)
	{
		global $core;

		$view = $this->options;
		$bind = null;
		$id = $this->id;
		$page = $this->page;

		if ($view['provider'])
		{
			list($constructor, $name) = explode('/', $id);

			$this->range = $this->init_range();

			$bind = $this->provide($this->options['provider'], $engine->context, $page->url_variables + $_GET); // FIXME-20120628: we should be using Request here

			$engine->context['this'] = $bind;
			$engine->context['range'] = $this->range;

			if (is_array($bind) && current($bind) instanceof Node)
			{
				Event::fire('nodes_load', array('nodes' => $bind), $engine);
			}
			else if ($bind instanceof Node)
			{
				Event::fire('nodes_load', array('nodes' => array($bind)), $engine);
			}
			else if (!$bind)
			{
				$this->element->add_class('empty');

				$rc = (string) $this->render_empty_inner_html();

				$this->fire_render_empty_inner_html
				(
					array
					(
						'id' => $id,
						'rc' => &$rc
					)
				);

				return $rc;
			}
		}

		#
		#
		#

		$rc = '';

		if (!$template_path)
		{
			throw new Exception('Unable to resolve template for view %id', array('id' => $id));
		}

		I18n::push_scope($this->module->flat_id);

		try
		{
			$extension = pathinfo($template_path, PATHINFO_EXTENSION);

			$module = $core->modules[$this->module_id];

			$engine->context['core'] = $core;
			$engine->context['document'] = $core->document;
			$engine->context['page'] = $page;
			$engine->context['module'] = $module;
			$engine->context['view'] = $this;

			$engine->context = $this->alter_context($engine->context);

			if ('php' == $extension)
			{
				$rc = null;

				ob_start();

				try
				{
					$isolated_require = function ($__file__, $__exposed__)
					{
						extract($__exposed__);

						require $__file__;
					};

					$isolated_require
					(
						$template_path, array
						(
							'bind' => $bind,
							'context' => &$engine->context,
							'core' => $core,
							'document' => $core->document,
							'page' => $page,
							'module' => $module,
							'view' => $this
						)
					);

					$rc = ob_get_clean();
				}
				catch (\ICanBoogie\Exception\Config $e)
				{
					$rc = '<div class="alert">' . $e->getMessage() . '</div>';

					ob_clean();
				}
				catch (\Exception $e)
				{
					ob_clean();

					throw $e;
				}
			}
			else if ('html' == $extension)
			{
				$template = file_get_contents($template_path);

				if ($template === false)
				{
					throw new \Exception("Unable to read template from <q>$template_path</q>");
				}

				$rc = $engine($template, $bind, array('file' => $template_path));

				if ($rc === null)
				{
					var_dump($template_path, file_get_contents($template_path), $rc);
				}
			}
			else
			{
				throw new Exception('Unable to process file %file, unsupported type', array('file' => $template_path));
			}
		}
		catch (\Exception $e)
		{
			I18n::pop_scope();

			throw $e;
		}

		I18n::pop_scope();

		return $rc;
	}

	protected $element;

	protected function volatile_get_element()
	{
		return $this->element;
	}

	protected function alter_element(Element $element)
	{
		return $element;
	}

	/**
	 * Returns the HTML representation of the view element and its content.
	 *
	 * @return string
	 */
	protected function render_outer_html()
	{
		$class = '';
		$type = \ICanBoogie\normalize($this->type);
		$m = $this->module;

		while ($m)
		{
			$normalized_id = \ICanBoogie\normalize($m->id);
			$class = "view--$normalized_id--$type $class";

			$m = $m->parent;
		}

		$this->element = new Element
		(
			'div', array
			(
				'id' => 'view-' . \ICanBoogie\normalize($this->id),
				'class' => trim("view view--$type $class"),
				'data-constructor' => $this->module->id
			)
		);

		$this->element = $this->alter_element($this->element);

// 		\ICanBoogie\log("class: {$this->element->class}, type: $type, assets: " . \ICanBoogie\dump($this->options['assets']));

		$template_path = $this->resolve_template_location();

		$html = $this->render_inner_html($template_path, $this->engine);

		if (preg_match('#\.html$#', $this->page->template))
		{
			$this->element[Element::INNER_HTML] = $html;

			$html = (string) $this->element;
		}

		return $html;
	}

	protected function resolve_template_location()
	{
		global $core;

		$id = $this->id;
		$type = $this->type;
		$templates = array();

		if (true)
		{
			/*
			$templates_base = array
			(
				str_replace('/', '--', str_replace($this->module->id . '/', '', str_replace(':', '-', $id))),
				$type
			);
			*/

			$templates_base = array();

			$parts = explode('/', $id);
			$module_id = array_shift($parts);
			$type = array_pop($parts);

			while (count($parts))
			{
				$templates_base[] = implode('--', $parts) . '--' . $type;

				array_pop($parts);
			}

			$templates_base[] = $type;

			$templates_base = array_unique($templates_base);

// 			\ICanBoogie\log('templates bases: \1', array($templates_base));

			$descriptors = $core->modules->descriptors;
			$descriptor = $descriptors[$this->module->id];

			while ($descriptor)
			{
				foreach ($templates_base as $template)
				{
					$pathname = \ICanBoogie\DOCUMENT_ROOT . 'protected/all/templates/views/' . \ICanBoogie\normalize($descriptor[Module::T_ID]) . '--' . $template;
					$templates[] = $pathname;

					$pathname = $descriptor[Module::T_PATH] . 'views/' . $template;
					$templates[] = $pathname;
				}

				$descriptor = $descriptor[Module::T_EXTENDS] ? $descriptors[$descriptor[Module::T_EXTENDS]] : null;
			}

			foreach ($templates_base as $template)
			{
				$pathname = \ICanBoogie\DOCUMENT_ROOT . 'protected/all/templates/views/' . $template;
				$templates[] = $pathname;
			}
		}

// 		\ICanBoogie\log('templates: \1', array($templates));

		$handled = array('php', 'html');

		foreach ($templates as $template)
		{
			foreach ($handled as $extension)
			{
				$pathname = $template . '.' . $extension;

// 				\ICanBoogie\log("tryed: $pathname");

				if (file_exists($pathname))
				{
					return $pathname;
				}
			}
		}

		throw new Exception('Unable to resolve template for view %id. Tried: :list', array('id' => $id, ':list' => implode("\n<br />", $templates)));
	}

	/**
	 * Checks if the view access is valid.
	 *
	 * @throws Exception\HTTP when the view access requires authentication.
	 *
	 * @return boolean true
	 */
	protected function validate_access()
	{
		$access_callback = $this->options['access_callback'];

		if ($access_callback && !call_user_func($access_callback, $this))
		{
			throw new Exception\HTTP
			(
				'The requested URL %uri requires authentication.', array
				(
					'%uri' => $_SERVER['REQUEST_URI']
				),

				401
			);
		}

		return true;
	}
}