<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Module\Nodes::alter.block.edit' => array
		(
			'ICanBoogie\Hooks\Sites\Firstposition::event_alter_block_edit'
		),

		'Icybee::render' => 'ICanBoogie\Hooks\Sites\Firstposition::on_icybee_render',

		'ICanBoogie\Operation\Pages\Export::process' => array
		(
			'ICanBoogie\Hooks\Sites\Firstposition::event_operation_export'
		),

		'BrickRouge\Document::render_title:before' => array
		(
			'ICanBoogie\Hooks\Sites\Firstposition::on_document_render_title', 'weight' => 10
		),

		'BrickRouge\Document\::render_metas:before' => array
		(
			'ICanBoogie\Hooks\Sites\Firstposition::before_document_render_metas', 'weight' => 10
		),

		'BrickRouge\Document\::render_metas' => array
		(
			'ICanBoogie\Hooks\Sites\Firstposition::on_document_render_metas', 'weight' => 10
		)
	),

	'patron.markups' => array
	(
		'document:metas' => array
		(
			'ICanBoogie\Hooks\Sites\Firstposition::markup_document_metas', array()
		),

		'document:title' => array
		(
			'ICanBoogie\Hooks\Sites\Firstposition::markup_document_title', array()
		),
	)
);