<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Hooks\Feedback;

class Hits
{
	public static function markup_hit(array $args, \WdPatron $patron, $template)
	{
		global $core, $document;

		if ($core->user_id == 1)
		{
			return;
		}

		$document->js->add('assets/hit.js');

		$key = uniqid();

		$core->session->modules['feedback.hits']['uniqid'] = $key;

		$select = $args['select'];
		$nid = is_object($select) ? $select->nid : $select;

		return <<<EOT
<script type="text/javascript">

var feedback_hits_nid = $nid;

</script>
EOT;
	}

	static public function markup_hits(array $args, \WdPatron $patron, $template)
	{
		global $core;

		$limit = $args['limit'];
		$constructor = $args['constructor'];

		$hits = $core->models['feedback.hits']->query
		(
			'SELECT hit.*, (hits / (TO_DAYS(CURRENT_DATE) - TO_DAYS(first))) AS perday
			FROM {self} as hit
			INNER JOIN {prefix}nodes USING(nid)
			WHERE is_online = 1 AND constructor = ?
			ORDER BY hits DESC LIMIT ' . $limit, array
			(
				$constructor
			)
		)
		->fetchAll(\PDO::FETCH_OBJ);

		$nids = array();

		foreach ($hits as $hit)
		{
			$nids[$hit->nid] = $hit;
		}

		$entries = $core->models[$constructor]->find(array_keys($nids));

		foreach ($entries as $entry)
		{
			$nids[$entry->nid]->node = $entry;
		}

		return $patron($template, array_values($nids));
	}
}