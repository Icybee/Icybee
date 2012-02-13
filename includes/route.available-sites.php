<?php

use Brickrouge;
use Brickrouge\Element;
use Brickrouge\Form;

function _route_add_available_sites()
{
	global $core;

	$document = $core->document;
	$document->page_title = 'Select a website';

	$ws_title = wd_entities($core->site->admin_title ? $core->site->admin_title : $core->site->title .':' . $core->site->language);
	$site_model = $core->models['sites'];

	$available = $site_model
	->where('siteid IN(' . implode(',', $core->user->sites_ids) . ')')
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
			Element::CHILDREN => array
			(
				new Element
				(
					'select', array
					(
						Element::LABEL => 'Available sites',
						Element::LABEL_POSITION => 'before',
						Element::OPTIONS => $options
					)
				),

				' &nbsp; ',

				new Button
				(
					'Change', array
					(
						'class' => 'continue',
						'type' => 'submit'
					)
				)
			),

			'name' => 'change-working-site'
		)
	);

	$rc = <<<EOT
<div class="group">
<h2>Access denied</h2>
<p>You don't have permission to access the administration interface for the website <q>$ws_title</q>,
please select another website to work with:</p>
$form
</div>
EOT;

	$core->document->addToBlock($rc, 'contents');
}