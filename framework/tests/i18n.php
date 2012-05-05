<?php

require_once 'Icybee/includes/startup.php';

echo t('confirm', array(':count' => 12), array('scope' => array('nodes', 'delete', 'operation'))) . '<br />';

echo t('system_nodes.delete.operation.cancel', array(), array('default' => array('delete.operation.cancel', 'operation.cancel'))) . '<br />';
echo t('cancel', array(), array('scope' => array('nodes', 'delete', 'operation'))) . '<br />';

echo t('contents', array(), array('scope' => 'system.modules.categories'));