<?php

return array
(
	'events' => array
	(
		'ICanBoogie\Module\System\Cache::alter.block.manage' => 'ICanBoogie\Hooks\Contents::alter_block_manage'
	),

	'objects.methods' => array
	(
		'ICanBoogie\Operation\System\Cache\Enable::enable_contents_body' => 'ICanBoogie\Hooks\Contents::enable_cache',
		'ICanBoogie\Operation\System\Cache\Disable::disable_contents_body' => 'ICanBoogie\Hooks\Contents::disable_cache',
		'ICanBoogie\Operation\System\Cache\Stat::stat_contents_body' => 'ICanBoogie\Hooks\Contents::stat_cache',
		'ICanBoogie\Operation\System\Cache\Clear::clear_contents_body' => 'ICanBoogie\Hooks\Contents::clear_cache'
	),

	'patron.markups' => array
	(
		'contents' => array
		(
			'o:contents_view_WdMarkup', array
			(
				'constructor' => 'contents',
				'select' => array('expression' => true, 'required' => true)
			)
		),

		'contents:home' => array
		(
			'o:contents_home_WdMarkup', array
			(
				'constructor' => 'contents'
			)
		),

		'contents:list' => array
		(
			'o:contents_list_WdMarkup', array
			(
				'constructor' => 'contents',
				'select' => array('expression' => true)
			)
		)
	)
);