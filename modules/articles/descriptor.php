<?php

use ICanBoogie\Module;

return array
(
	Module::T_TITLE => 'Articles',
	Module::T_DESCRIPTION => 'Articles management',
	Module::T_CATEGORY => 'contents',
	Module::T_EXTENDS => 'contents',

	Module::T_MODELS => array
	(
		'primary' => 'inherit'
	)
);