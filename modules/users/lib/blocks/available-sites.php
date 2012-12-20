<?php

namespace Icybee\Modules\Users;

use Brickrouge\Button;
use Brickrouge\Element;
use Brickrouge\Form;

class AvailableSitesBlock extends Element
{
	public function render()
	{
		global $core;

		$document = $core->document;
		$document->js->add('available-sites.js');
		$document->page_title = 'Select a website';

		$ws_title = \ICanBoogie\escape($core->site->admin_title ? $core->site->admin_title : $core->site->title .':' . $core->site->language);
		$site_model = $core->models['sites'];

		$available = $site_model
		->where('siteid IN(' . implode(',', $core->user->restricted_sites_ids) . ')')
		->order('admin_title, title')
		->all;

		$uri = substr($_SERVER['REQUEST_URI'], strlen($core->site->path));
		$options = array();

		foreach ($available as $site)
		{
			$title = $site->title . ':' . $site->language;

			if ($site->admin_title)
			{
				$title .= ' (' . $site->admin_title . ')';
			}

			$options[$site->url . $uri] = $title;
		}

		$form = new Form
		(
			array
			(
				Form::ACTIONS => new Button
				(
					'Change', array
					(
						'class' => 'btn-primary',
						'type' => 'submit'
					)
				),

				Form::RENDERER => 'Simple',

				Element::CHILDREN => array
				(
					new Element
					(
						'select', array
						(
							Element::DESCRIPTION => "Select one of the website available to your profile.",
							Element::OPTIONS => $options
						)
					)
				),

				'name' => 'change-working-site',
				'class' => 'form-primary'
			)
		);

		return <<<EOT
<div id="block--site-access-denied" class="block-alert">
<h2>Access denied</h2>
<p>You don't have permission to access the administration interface for the website <q>$ws_title</q>,
please select another website to work with:</p>
$form
</div>
EOT;
	}
}