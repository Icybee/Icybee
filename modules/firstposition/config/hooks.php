<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\Nodes\Module::alter.block.edit' => 'ICanBoogie\Modules\Firstposition\Hooks::event_alter_block_edit',
		'Icybee::render' => 'ICanBoogie\Modules\Firstposition\Hooks::on_icybee_render',
		'ICanBoogie\Operation\Pages\Export::process' => 'ICanBoogie\Modules\Firstposition\Hooks::event_operation_export',
		'BrickRouge\Document::render_title:before' => 'ICanBoogie\Modules\Firstposition\Hooks::on_document_render_title',
		'BrickRouge\Document\::render_metas:before' => 'ICanBoogie\Modules\Firstposition\Hooks::before_document_render_metas',
		'BrickRouge\Document\::render_metas' => 'ICanBoogie\Modules\Firstposition\Hooks::on_document_render_metas'
	),

	'patron.markups' => array
	(
		'document:metas' => array
		(
			'ICanBoogie\Modules\Firstposition\Hooks::markup_document_metas', array()
		),

		'document:title' => array
		(
			'ICanBoogie\Modules\Firstposition\Hooks::markup_document_title', array()
		),
	)
);