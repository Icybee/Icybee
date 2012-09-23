<?php

namespace Icybee\Modules\Taxonomy\Vocabulary;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\SaveOperation::process' => $hooks . 'on_node_save',
		'ICanBoogie\Modules\Nodes\EditBlock::alter_children' => $hooks . 'on_nodes_editblock_alter_children',
		'ICanBoogie\ActiveRecord\Node::property' => $hooks . 'get_term',
		'Icybee\Modules\Pages\BreadcrumbElement::render_inner_html:before' => $hooks . 'before_breadcrumb_render_inner_html',
		'Icybee\Modules\Views\Collection::collect' => $hooks . 'on_collect_views',
		'Icybee\Modules\Views\Provider::alter_query' => $hooks . 'on_alter_provider_query',
	)
);