<?php

if (file_exists(ICanBoogie\REPOSITORY . 'vars/default_nodes_routes'))
{
	return require ICanBoogie\REPOSITORY . 'vars/default_nodes_routes';
}

return array();