<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Icybee\Modules\Dashboard;

use ICanBoogie\Event;

class Module extends \ICanBoogie\Module
{
	protected function block_dashboard()
	{
		global $core;

		$core->document->title = 'Dashboard';
		$core->document->css->add('/public/dashboard.css');
		$core->document->js->add('/public/dashboard.js');

		$panels = $core->configs->synthesize('dashboard', 'merge');

		foreach ($panels as $i => $panel)
		{
			$panels[$i] += array
			(
				'column' => 0,
				'weight' => 0
			);
		}

		$user_config = $core->user->metas['dashboard.order'];

		if ($user_config)
		{
			$user_config = json_decode($user_config);

			foreach ($user_config as $column_index => $user_panels)
			{
				foreach ($user_panels as $panel_weight => $panel_id)
				{
					$panels[$panel_id]['column'] = $column_index;
					$panels[$panel_id]['weight'] = $panel_weight;
				}
			}
		}

		uasort($panels, function($a, $b) { return $a['weight'] - $b['weight']; });

		#
		#
		#

		$colunms = array
		(
			array(),
			array()
		);

		// config sign: ⚙

		foreach ($panels as $id => $descriptor)
		{
			try
			{
				if (empty($descriptor['callback']))
				{
					continue;
				}

				$callback = $descriptor['callback'];

				if (is_array($callback) && $callback[0]{1} == ':' && $callback[0]{0} == 'm')
				{
					$module_id = substr($callback[0], 2);

					if (empty($core->modules[$module_id]))
					{
						continue;
					}

					$callback[0] = $core->modules[$module_id];
				}

				$contents = call_user_func($callback);
			}
			catch (\Exception $e)
			{
				$contents = $e->getMessage();
			}

			if (!$contents)
			{
				continue;
			}

			$title = t($id, array(), array('scope' => array('dashboard', 'title'), 'default' => $descriptor['title']));

			$panel = <<<EOT
	<div class="panel" id="$id">
		<div class="panel-title">$title</div>
		<div class="panel-contents">$contents</div>
	</div>
EOT;

			$colunms[$descriptor['column']][] = $panel;
		}

		$rc = '<div id="dashboard"><div id="dashboard-panels">';

		foreach ($colunms as $i => $panels)
		{
			$panels = implode(PHP_EOL, $panels);

			$rc .= <<<EOT
<div class="column">
	$panels
	<div class="panel-holder">&nbsp;</div>
</div>
EOT;
		}

		$rc .= '</div></div>';

		return $rc;
	}
}