<?php

use BrickRouge;
use BrickRouge\Element;
use BrickRouge\Form;

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
			Element::T_CHILDREN => array
			(
				new Element
				(
					'select', array
					(
						Element::T_LABEL => 'Available sites',
						Element::T_LABEL_POSITION => 'before',
						Element::T_OPTIONS => $options
					)
				),

				' &nbsp; ',

				new Element
				(
					Element::E_SUBMIT, array
					(
						Element::T_INNER_HTML => 'Change',

						'class' => 'continue'
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