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
use ICanBoogie\Routing;

use Brickrouge\Element;
use Brickrouge\ElementIsEmpty;

use Icybee\Modules\Users\User;

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
		parent::__construct('div', $attributes + [

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

	/**
	 * Returns an empty string if the user is a guest or a member.
	 */
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

		$page = $core->request->context->page;
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
		$user = $core->user;

		# header

		$html = $this->render_header($translator, $user, $edit_target);

		# nodes

		$nodes = $this[self::NODES];

		if ($nodes)
		{
			if ($edit_target)
			{
				unset($nodes[$edit_target->nid]);
			}

			$html .= $this->render_panel_nodes($nodes, $translator, $user, $edit_target);
		}

		# config

		$html .= $this->render_panel_config($translator);

		#

		if (!$html)
		{
			throw new ElementIsEmpty();
		}

		$admin_path = Routing\contextualize('/admin/');

		return <<<EOT
<div class="panel-title"><a href="$admin_path">Icybee</a></div>
<div class="contents">$html</div>
EOT;
	}

	protected function render_header(Proxi $translator, User $user, $edit_target)
	{
		global $core;

		$html = '<ul style="text-align: center;"><li>';

		if ($user->has_permission(Module::PERMISSION_MAINTAIN, $edit_target->constructor))
		{
			$href = Routing\contextualize('/admin/' . $edit_target->constructor . '/' . $edit_target->nid . '/edit');
			$title = $translator('Edit: !title', [ 'title' => $edit_target->title ]);
			$label = $translator('Edit');

			$html .= <<<EOT
<a href="$href" title="$title">$label</a> &ndash;
EOT;
		}

		$html .= ' <a href="' . Operation::encode('users/logout', [ 'location'  => $_SERVER['REQUEST_URI'] ]) . '">' . $translator('Disconnect') . '</a> &ndash;';
		$html .= ' <a href="' . Routing\contextualize('/admin/') . '">' . $translator('Admin') . '</a></li>';
		$html .= '</ul>';

		return $html;
	}

	protected function render_panel_config(Proxi $translator)
	{
		global $core;

		$links = [];

		$routes = $core->routes;
		$site = $core->site;

		foreach ($core->modules as $module_id => $module)
		{
			$id = "admin:$module_id/config";

			if (empty($routes[$id]))
			{
				continue;
			}

			$href = \ICanBoogie\escape(Routing\contextualize($routes[$id]));

			$label = $translator($module->flat_id, [], [

				'scope' => 'module_title',
				'default' => $module->title

			]);

			$links[] = <<<EOT
<a href="$href">$label</a>
EOT;
		}

		if (!$links)
		{
			return;
		}

		$links = implode('</li><li>', $links);

		return <<<EOT
<div class="panel-section-title">Configurer</div>
<ul><li>$links</li></ul>
EOT;
	}

	protected function render_panel_nodes(array $nodes, Proxi $translator, User $user)
	{
		global $core;

		$editables_by_category = [];
		$descriptors = $core->modules->descriptors;

		$translator->scope = 'module_category';

		foreach ($nodes as $node)
		{
			if (!$user->has_permission(Module::PERMISSION_MAINTAIN, $node->constructor))
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
		$html = '';

		foreach ($editables_by_category as $category => $nodes)
		{
			$html .= '<div class="panel-section-title">' . \ICanBoogie\escape($category) . '</div>';
			$html .= '<ul>';

			foreach ($nodes as $node)
			{
				$url = Routing\contextualize('/admin/' . $node->constructor . '/' . $node->nid . '/edit');
				$title = $translator->__invoke('Edit: !title', array('!title' => $node->title));
				$label = \ICanBoogie\escape(\ICanBoogie\shorten($node->title));

				$html .= <<<EOT
<li><a href="$url" title="$title">$label</a></li>
EOT;
			}

			$html .= '</ul>';
		}

		return $html;
	}
}