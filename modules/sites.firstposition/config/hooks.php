<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Module\Nodes::alter.block.edit' => 'ICanBoogie\Hooks\Sites\Firstposition::event_alter_block_edit',
		'Icybee::render' => 'ICanBoogie\Hooks\Sites\Firstposition::on_icybee_render',
		'ICanBoogie\Operation\Pages\Export::process' => 'ICanBoogie\Hooks\Sites\Firstposition::event_operation_export',
		'BrickRouge\Document::render_title:before' => 'ICanBoogie\Hooks\Sites\Firstposition::on_document_render_title',
		'BrickRouge\Document\::render_metas:before' => 'ICanBoogie\Hooks\Sites\Firstposition::before_document_render_metas',
		'BrickRouge\Document\::render_metas' => 'ICanBoogie\Hooks\Sites\Firstposition::on_document_render_metas'
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