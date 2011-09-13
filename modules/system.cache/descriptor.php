<?php

use ICanBoogie\Module;

return array
(
	Module::T_CATEGORY => 'features',
	Module::T_DESCRIPTION => 'Provides a unified cache system',
	Module::T_PERMISSIONS => array('administer system cache'),
	Module::T_REQUIRED => true,
	Module::T_TITLE => 'Cache'
);