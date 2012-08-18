<?php

return array
(
	'patron.markups' => 
	
	(require $path . 'markups/feed.hooks.php') +
	(require $path . 'markups/native.hooks.php') +
	(require $path . 'markups/elements.hooks.php')
);