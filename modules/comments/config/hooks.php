<?php

namespace Icybee\Modules\Comments;

$hooks = __NAMESPACE__ . '\Hooks::';

return array
(
	'events' => array
	(
		'Icybee\Modules\Nodes\SaveOperation::process:before' => $hooks . 'before_node_save',
		'Icybee\Modules\Nodes\DeleteOperation::process' => $hooks . 'on_node_delete',
		'Icybee\Modules\Forms\Module::alter.block.edit' => $hooks . 'alter_block_edit', // FIXME-20120922: this event is no longer fired
		'Icybee\Modules\Views\View::render' => $hooks . 'on_view_render'
	),

	'prototypes' => array
	(
		'Icybee\Modules\Nodes\Node::get_comments' => $hooks . 'get_comments',
		'Icybee\Modules\Nodes\Node::get_comments_count' => $hooks . 'get_comments_count',
		'Icybee\Modules\Nodes\Node::get_rendered_comments_count' => $hooks . 'get_rendered_comments_count'
	),

	'patron.markups' => array
	(
		'comments' => array
		(
			$hooks . 'markup_comments', array
			(
				'node' => null,
				'order' => 'created asc',
				'limit' => 0,
				'page' => 0,
				'noauthor' => false,
				'parseempty' => false
			)
		),

		'comments:form' => array
		(
			$hooks . 'markup_form', array
			(
				'select' => array('expression' => true, 'default' => 'this', 'required' => true)
			)
		)
	)
);