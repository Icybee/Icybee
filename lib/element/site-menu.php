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

use ICanBoogie\Route;

use Brickrouge\A;
use Brickrouge\DropdownMenu;
use Brickrouge\Element;

class SiteMenu extends Element
{
	public function __construct(array $attributes=array())
	{
		parent::__construct('div', $attributes);
	}

	public function render_inner_html()
	{
		global $core;

		$user = $core->user;
		$site = $core->site;

		$site_title = \ICanBoogie\escape($site->admin_title);

		if (!$site_title)
		{
			$site_title = \ICanBoogie\escape($site->title) . '<span class="language">:' . $site->language . '</span>';
		}

		$options = array();

		try
		{
			$query = $core->models['sites']->order('admin_title, title');

			$restricted_sites = $user->restricted_sites_ids;

			if ($restricted_sites)
			{
				$query->where(array('siteid' => $restricted_sites));
			}

			$sites = $query->all;

			if (count($sites) > 1)
			{
				$path = $core->request->decontextualized_path;

				foreach ($sites as $asite)
				{
					$title = $asite->admin_title;

					if (!$title)
					{
						$title = new Element('span', array(Element::INNER_HTML => $asite->title . '<span class="language">:' . $asite->language . '</span>'));
					}

					$options[$asite->siteid] = new A($title, $asite->url . $path . '?ssc=1');
				}
			}
		}
		catch (\Exception $e) { /**/ }

		$menu = null;
		$menu_toggler = null;

		if ($options)
		{
			$menu = new DropdownMenu(array
			(
				DropdownMenu::OPTIONS => $options,

				'value' => $site->siteid
			));

			$menu_toggler = <<<EOT
<span class="dropdown-toggle" data-toggle="dropdown"><i class="icon-home icon-white"></i> <span class="caret"></span></span>
EOT;
		}
		else
		{
			$menu_toggler = <<<EOT
<i class="icon-home icon-white"></i>
EOT;
		}

		return <<<EOT
<div class="btn-group">
	<a href="$site->url">$site_title</a>
	$menu_toggler
	$menu
</div>
EOT;
	}
}