<?php

namespace ICanBoogie\Modules\Taxonomy\Vocabulary\Hooks;

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\SaveOperation::process' => __NAMESPACE__ . '::on_node_save',
		'ICanBoogie\Modules\Nodes\EditBlock::alter_children' => __NAMESPACE__ . '::on_nodes_editblock_alter_children',
		'ICanBoogie\ActiveRecord\Node::property' => __NAMESPACE__ . '::get_term',
		'ICanBoogie\Modules\Pages\BreadcrumbElement::render_inner_html:before' => __NAMESPACE__ . '::before_breadcrumb_render_inner_html',

		'Icybee\Views::alter' => __NAMESPACE__ . '::on_alter_views',
		'Icybee\Views\Provider::alter_query' => __NAMESPACE__ . '::on_alter_provider_query',
	)
);