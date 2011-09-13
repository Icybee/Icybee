<?php

use ICanBoogie\Module;
use ICanBoogie\ActiveRecord\Model;

return array
(
	Module::T_TITLE => 'Comments',
	Module::T_DESCRIPTION => 'Implements comments for nodes',
	Module::T_CATEGORY => 'feedback',
	Module::T_MODELS => array
	(
		'primary' => array
		(
			Model::T_SCHEMA => array
			(
				'fields' => array
				(
					'commentid' => 'serial',
					'nid' => 'foreign',
					'parentid' => 'foreign', // for nested comments
					'uid' => 'foreign',
					'author' => array('varchar', 32),
					'author_email' => array('varchar', 64),
					'author_url' => 'varchar',
					'author_ip' => array('varchar', 45),
					'contents' => 'text',
					'status' => array('enum', array('pending', 'approved', 'spam'), 'indexed' => true),
					'notify' => array('enum', array('no', 'yes', 'author', 'done'), 'indexed' => true),
					'created' => array('timestamp', 'default' => 'current_timestamp()'),
				)
			)
		)
	)
);

/*
 * About ENUM performance: http://www.mysqlperformanceblog.com/2008/01/24/enum-fields-vs-varchar-vs-int-joined-table-what-is-faster/
 */