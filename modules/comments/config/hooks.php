<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Operation\Nodes\Save::process:before' => 'ICanBoogie\Hooks\Comments::before_node_save',
		'ICanBoogie\Operation\Nodes\Delete::process' => 'ICanBoogie\Hooks\Comments::on_node_delete',
		'ICanBoogie\Module\Forms::alter.block.edit' => 'ICanBoogie\Hooks\Comments::alter_block_edit',
		'ICanBoogie\View::render' => 'ICanBoogie\Hooks\Comments::on_view_render'
	),

	'objects.methods' => array
	(
		'ICanBoogie\ActiveRecord\Node::__get_comments' => 'ICanBoogie\Hooks\Comments::get_comments',
		'ICanBoogie\ActiveRecord\Node::__get_comments_count' => 'ICanBoogie\Hooks\Comments::get_comments_count',
		'ICanBoogie\ActiveRecord\Node::__get_rendered_comments_count' => 'ICanBoogie\Hooks\Comments::get_rendered_comments_count'
	),

	'patron.markups' => array
	(
		'feedback:comments' => array
		(
			array('ICanBoogie\Hooks\Comments', 'comments'), array
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
			array('ICanBoogie\Hooks\Comments', 'form'), array
			(
				'select' => array('expression' => true, 'default' => 'this', 'required' => true)
			)
		)
	)
);