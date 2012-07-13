<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Modules\System\Cache\Collection::alter' => 'Icybee\Hooks::on_alter_cache_collection',
		'ICanBoogie\Modules\Users\LogoutOperation::process:before' => 'Icybee\Hooks::before_user_logout',
		'ICanBoogie\Operation::get_form' => 'Icybee\Element\Form::on_operation_get_form',

		'ICanBoogie\Modules\System\Modules\ActivateOperation::process' => 'Icybee\Views\CacheManager::on_modules_activate',
		'ICanBoogie\Modules\System\Modules\DeactivateOperation::process' => 'Icybee\Views\CacheManager::on_modules_deactivate',

		'Patron\Engine::nodes_load' => 'Icybee\Pagemaker::on_nodes_load',
		'Icybee::nodes_load' => 'Icybee\Pagemaker::on_nodes_load'
	),

	'prototypes' => array
	(
		'ICanBoogie\Core::get_document' => 'Icybee\Document::hook_get_document'
	),

	'patron.markups' => array
	(
		'document:metas' => array
		(
			'Icybee\Document::markup_document_metas', array()
		),

		'document:title' => array
		(
			'Icybee\Document::markup_document_title', array()
		),

		'alerts' => array
		(
			'Icybee\Hooks::markup_alerts', array()
		)
	)
);