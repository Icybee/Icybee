<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\SaveOperation::process:before' => 'ICanBoogie\Modules\Comments\Hooks::before_node_save',
		'ICanBoogie\Modules\Nodes\DeleteOperation::process' => 'ICanBoogie\Modules\Comments\Hooks::on_node_delete',
		'ICanBoogie\Modules\Forms\Module::alter.block.edit' => 'ICanBoogie\Modules\Comments\Hooks::alter_block_edit',
		'Icybee\Modules\Views\View::render' => 'ICanBoogie\Modules\Comments\Hooks::on_view_render'
	),

	'prototypes' => array
	(
		'ICanBoogie\ActiveRecord\Node::get_comments' => 'ICanBoogie\Modules\Comments\Hooks::get_comments',
		'ICanBoogie\ActiveRecord\Node::get_comments_count' => 'ICanBoogie\Modules\Comments\Hooks::get_comments_count',
		'ICanBoogie\ActiveRecord\Node::get_rendered_comments_count' => 'ICanBoogie\Modules\Comments\Hooks::get_rendered_comments_count'
	),

	'patron.markups' => array
	(
		'feedback:comments' => array
		(
			'ICanBoogie\Modules\Comments\Hooks::comments', array
			(
				'node' => null,
				'order' => 'created asc',
				'limit' => 0,
				'page' => 0,
				'noauthor' => false,
				'parseempty' => false
			)
		),

		'feedback:comments:form' => array
		(
			'ICanBoogie\Modules\Comments\Hooks::form', array
			(
				'select' => array('expression' => true, 'default' => 'this', 'required' => true)
			)
		)
	)
);