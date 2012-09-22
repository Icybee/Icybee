<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Forms;

use Brickrouge\Element;

class PopForm extends Element
{
	public function __toString()
	{
		global $core;

		try
		{
			$site = $core->site;
			$value = (int) $this['value'];

			$options = $core->models['forms']->select('nid, title')
			->where('nid = ? OR ((siteid = 0 OR siteid = ?) AND (language = "" OR language = ?))', $value, $site->siteid, $site->language)
			->order('title')
			->pairs;

			if (!$options)
			{
				$url = \Brickrouge\escape($core->site->path . '/admin/forms/new');

				return <<<EOT
<a href="$url" class="btn btn-info">Créer un premier formulaire...</a>
EOT;
			}

			if ($this->type == 'select')
			{
				$options = array(null => '') + $options;
			}

			$this[self::OPTIONS] = $options;
		}
		catch (\Exception $e)
		{
			return \Brickrouge\render_exception($e);
		}

		return parent::__toString();
	}
}