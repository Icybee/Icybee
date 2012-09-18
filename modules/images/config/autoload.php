<?php

$widgets_path = $path . 'widgets' . DIRECTORY_SEPARATOR;

return array
(
	'Brickrouge\Widget\AdjustImage' => $widgets_path . 'adjust-image.php',
	'Brickrouge\Widget\PopImage' => $widgets_path . 'pop-image.php',
	'Brickrouge\Widget\ImageUpload' => $widgets_path . 'image-upload.php',
	'Brickrouge\Widget\AdjustThumbnail' => $widgets_path . 'adjust-thumbnail.php',

	'ICanBoogie\Modules\Images\GalleryManager' => $path . 'gallery.manager.php'
);