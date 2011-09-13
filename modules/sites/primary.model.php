<?php

namespace ICanBoogie\ActiveRecord\Model;

use ICanBoogie\ActiveRecord\Model;

class Sites extends Model
{
	public function save(array $properties, $key=null, array $options=array())
	{
		if (isset($properties['path']))
		{
			$path = trim($properties['path'], '/');

			if ($path)
			{
				$path = '/' . $path;
			}

			$properties['path'] = $path;
		}

		return parent::save($properties, $key, $options);
	}
}