<?php

$widgets_path = $path . 'widgets' . DIRECTORY_SEPARATOR;

return array
(
	'autoload' => array
	(
		'BrickRouge\Widget\AdjustImage' => $widgets_path . 'adjust-image.php',
		'BrickRouge\Widget\PopImage' => $widgets_path . 'pop-image.php',
		'BrickRouge\Widget\ImageUpload' => $widgets_path . 'image-upload.php',
		'BrickRouge\Widget\AdjustThumbnail' => $widgets_path . 'adjust-thumbnail.php',

		'Icybee\Manager\Images\Gallery' => $path . 'gallery.manager.php'
	)
);