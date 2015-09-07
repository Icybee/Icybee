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

use Brickrouge\A;
use Brickrouge\DropdownMenu;
use Brickrouge\Element;
use Icybee\Binding\PrototypedBindings;

/**
 * @property-read string $decontextualized_path
 * @property-read \ICanBoogie\Module\ModelCollection $models
 * @property-read \Icybee\Modules\Sites\Site $site
 * @property-read \Icybee\Modules\Users\User $user
 */
class SiteMenu extends Element
{
	use PrototypedBindings;

	protected function get_models()
	{
		return $this->app->models;
	}

	protected function get_decontextualized_path()
	{
		return $this->app->request->decontextualized_path;
	}

	protected function get_site()
	{
		return $this->app->site;
	}

	protected function get_user()
	{
		return $this->app->user;
	}

	public function __construct(array $attributes = [])
	{
		parent::__construct('div', $attributes);
	}

	public function render_inner_html()
	{
		$user = $this->user;
		$site = $this->site;

		$site_title = \ICanBoogie\escape($site->admin_title);

		if (!$site_title)
		{
			$site_title = \ICanBoogie\escape($site->title) . '<span class="language">:' . $site->language . '</span>';
		}

		$options = [];

		try
		{
			$query = $this->models['sites']->order('admin_title, title');

			$restricted_sites = $user->restricted_sites_ids;

			if ($restricted_sites)
			{
				$query->where([ 'site_id' => $restricted_sites ]);
			}

			$sites = $query->all;

			if (count($sites) > 1)
			{
				$path = $this->decontextualized_path;

				foreach ($sites as $asite)
				{
					$title = $asite->admin_title;

					if (!$title)
					{
						$title = new Element('span', [ Element::INNER_HTML => $asite->title . '<span class="language">:' . $asite->language . '</span>' ]);
					}

					$options[$asite->site_id] = new A($title, $asite->url . $path . '?ssc=1');
				}
			}
		}
		catch (\Exception $e) { /**/ }

		$menu = null;
		$menu_toggler = null;

		if ($options)
		{
			$menu = new DropdownMenu([

				DropdownMenu::OPTIONS => $options,

				'value' => $site->site_id

			]);

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
