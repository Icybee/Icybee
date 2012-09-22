<?php

namespace Icybee\Modules\Images;

$widgets_path = $path . 'widgets' . DIRECTORY_SEPARATOR;

return array
(
	__NAMESPACE__ . '\AdjustImage' => $path . 'lib/elements/adjust-image.php',
	__NAMESPACE__ . '\AdjustThumbnail' => $path . 'lib/elements/adjust-thumbnail.php',
	__NAMESPACE__ . '\ImageUpload' => $path . 'lib/elements/image-upload.php',
	__NAMESPACE__ . '\PopImage' => $path . 'lib/elements/pop-image.php',

	'Brickrouge\Widget\AdjustImage' => $path . 'lib/elements/adjust-image.php', // TODO-20120922: COMPAT with /api/widgets/{class}/popup
	'Brickrouge\Widget\AdjustThumbnail' => $path . 'lib/elements/adjust-thumbnail.php', // TODO-20120922: COMPAT with /api/widgets/{class}/popup

	'Icybee\Modules\Images\GalleryManager' => $path . 'gallery.manager.php'
);