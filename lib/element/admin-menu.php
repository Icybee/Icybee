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

use ICanBoogie\I18n\Translator\Proxi;
use ICanBoogie\Module;
use ICanBoogie\Operation;
use ICanBoogie\Route;

use Brickrouge\Element;
use Brickrouge\ElementIsEmpty;

/**
 * A menu that helps managing the contents of pages.
 *
 * @property \ICanBoogie\I18n\Translator\Proxi $translator
 */
class AdminMenu extends Element
{
	const NODES = '#nodes';

	static protected function add_assets(\Brickrouge\Document $document)
	{
		parent::add_assets($document);

		$document->css->add(\Icybee\ASSETS . 'css/admin-menu.css');
	}

	public function __construct(array $attributes=array())
	{
		parent::__construct('div', [

			'id' => 'icybee-admin-menu'

		]);
	}

	protected function lazy_get_translator()
	{
		global $core;

		$user = $core->user;
		$translator = new Proxi();

		if ($user->language)
		{
			$translator->language = $user->language;
		}

		return $translator;
	}

	public function render()
	{
		global $core;

		if (!$core->user_id || $core->user instanceof \Icybee\Modules\Members\Member)
		{
			return '';
		}

		return parent::render();
	}

	protected function render_inner_html()
	{
		global $core;

		$user = $core->user;
		$page = $core->request->context->page;

		$contents = null;
		$edit_target = $page->node ?: $page;

		if (!$edit_target)
		{
			#
			# when the page is cached 'page' is null because it is not loaded, we should load
			# the page ourselves to present the admin menu on cached pages.
			#

			throw new ElementIsEmpty();
		}

		$translator = $this->translator;

		$contents .= '<ul style="text-align: center;"><li>';

		if ($user->has_permission(Module::PERMISSION_MAINTAIN, $edit_target->constructor))
		{
			$contents .= '<a href="' . $core->site->path . '/admin/' . $edit_target->constructor . '/' . $edit_target->nid . '/edit' . '" title="' . $translator('Edit: !title', array('!title' => $edit_target->title)) . '">' . $translator('Edit') . '</a> &ndash; ';
		}

		$contents .= '<a href="' . \ICanBoogie\escape(Operation::encode('users/logout', array('location'  => $_SERVER['REQUEST_URI']))) . '">' . $translator('Disconnect') . '</a> &ndash;
		<a href="' . $core->site->path . '/admin/">' . $translator('Admin') . '</a></li>';
		$contents .= '</ul>';

		#
		# configurable
		#

		$routes = $core->routes;

		$links = array();
		$site = $core->site;

		foreach ($core->modules as $module_id => $module)
		{
			$id = "admin:$module_id/config";

			if (empty($routes[$id]))
			{
				continue;
			}

			$pathname = $routes[$id]->pattern;

			$links[] = '<a href="' . \ICanBoogie\escape(\ICanBoogie\Routing\contextualize($pathname)) . '">'
			. $translator($module->flat_id, array(), array('scope' => 'module_title', 'default' => $module->title)) . '</a>';
		}

		if ($links)
		{
			$contents .= '<div class="panel-section-title">Configurer</div>';
			$contents .= '<ul><li>' . implode('</li><li>', $links) . '</li></ul>';
		}

		#

		if ($this[self::NODES])
		{
			$editables_by_category = array();
			$descriptors = $core->modules->descriptors;

			$nodes = array();

			foreach ($this[self::NODES] as $node)
			{
				$nodes[$node->nid] = $node;
			}

			$translator->scope = 'module_category';

			foreach ($nodes as $node)
			{
				if ($node->nid == $edit_target->nid || !$user->has_permission(Module::PERMISSION_MAINTAIN, $node->constructor))
				{
					continue;
				}

				// TODO-20101223: use the 'language' attribute whenever available to translate the
				// categories in the user's language.

				$category = isset($descriptors[$node->constructor][Module::T_CATEGORY]) ? $descriptors[$node->constructor][Module::T_CATEGORY] : 'contents';
				$category = $translator($category);

				$editables_by_category[$category][] = $node;
			}

			$translator->scope = null;

			foreach ($editables_by_category as $category => $nodes)
			{
				$contents .= '<div class="panel-section-title">' . \ICanBoogie\escape($category) . '</div>';
				$contents .= '<ul>';

				foreach ($nodes as $node)
				{
					$contents .= '<li><a href="' . \ICanBoogie\Routing\contextualize('/admin/' . $node->constructor . '/' . $node->nid . '/edit') . '" title="' . $translator->__invoke('Edit: !title', array('!title' => $node->title)) . '">' . \ICanBoogie\escape(\ICanBoogie\shorten($node->title)) . '</a></li>';
				}

				$contents .= '</ul>';
			}
		}

		$rc = '';

		if ($contents)
		{
			$admin_path = \ICanBoogie\Routing\contextualize('/admin/');

			$rc  = <<<EOT
<div class="panel-title"><a href="$admin_path">Icybee</a></div>
<div class="contents">$contents</div>
EOT;
		}
		else
		{
			throw new ElementIsEmpty();
		}

		return $rc;
	}
}