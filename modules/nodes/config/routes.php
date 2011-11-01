<?php

if (file_exists(ICanBoogie\DOCUMENT_ROOT . '/repository/lib/default_nodes_routes'))
{
	return require ICanBoogie\DOCUMENT_ROOT . '/repository/lib/default_nodes_routes';
}

return array();