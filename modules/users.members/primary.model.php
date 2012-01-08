<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\ActiveRecord\Model\Users;

class Members extends \ICanBoogie\ActiveRecord\Model\Users
{
	public function save(array $properties, $key=null, array $options=array())
	{
		global $core;

		$photo = null;
		$photo_path = null;

		if (isset($properties['photo']) && is_object($properties['photo']))
		{
			$photo = $properties['photo'];

//			wd_log('photo: \1', array($photo));

			$filename = wd_normalize($properties['username']) . $photo->extension;
			$photo_path = $core->config['repository'] . '/files/members/' . $filename;
			$properties['photo'] = $photo_path;
		}

		$rc = parent::save($properties, $key, $options);

//		wd_log('photo: \1, properties: \2', array($photo, $properties));

		if ($rc && $photo)
		{
			$photo->move($_SERVER['DOCUMENT_ROOT'] . $photo_path, true);
		}

		return $rc;
	}
}