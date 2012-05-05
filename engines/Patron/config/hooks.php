<?php

return array
(
	'patron.markups' => 
	
	(require $root . 'markups/feed.hooks.php') +
	(require $root . 'markups/native.hooks.php') +
	(require $root . 'markups/elements.hooks.php')
);