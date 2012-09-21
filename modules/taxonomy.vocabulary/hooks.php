<?php

namespace ICanBoogie\Modules\Taxonomy\Vocabulary;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Event;
use ICanBoogie\Modules;
use ICanBoogie\Operation;

use Brickrouge;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

use Icybee\Modules\Views\Collection as ViewsCollection;
use Icybee\Modules\Views\Provider;

class Hooks
{
	protected static $cache_ar_vocabularies = array();
	protected static $cache_ar_terms = array();

	public static function get_term(\ICanBoogie\Object\PropertyEvent $event, ActiveRecord\Node $target)
	{
		global $core;

		$constructor = $target->constructor;
		$property = $vocabularyslug = $event->property;
		$siteid = $target->siteid;

		$use_slug = false;

		if (substr($property, -4, 4) == 'slug')
		{
			$use_slug = true;
			$vocabularyslug = substr($property, 0, -4);
		}

		$key = $siteid . '-' . $constructor . '-' . $vocabularyslug;

		if (!isset(self::$cache_ar_vocabularies[$key]))
		{
			self::$cache_ar_vocabularies[$key] = $core->models['taxonomy.vocabulary']
			->joins('INNER JOIN {self}__scopes USING(vid)')
			->where('constructor = ? AND vocabularyslug = ? AND (siteid = 0 OR siteid = ?)', (string) $constructor, $vocabularyslug, $target->siteid)
			->order('siteid DESC')
			->one;
		}

		$vocabulary = self::$cache_ar_vocabularies[$key];

		if (!$vocabulary)
		{
			return;
		}

		if ($vocabulary->is_required)
		{
			$event->value = 'uncategorized';
		}

		if (!isset(self::$cache_ar_terms[$key]))
		{
			$terms_model = $core->models['taxonomy.terms'];

			$terms = $terms_model->query
			(
				'SELECT term.*, (SELECT GROUP_CONCAT(nid) FROM {self}__nodes tnode WHERE tnode.vtid = term.vtid) AS nodes_ids
				FROM {self} term WHERE vid = ? ORDER BY weight, term', array
				(
					$vocabulary->vid
				)
			)
			->fetchAll(\PDO::FETCH_CLASS, 'ICanBoogie\ActiveRecord\Taxonomy\Term', array($terms_model));

			foreach ($terms as $term)
			{
				$term->nodes_ids = array_flip(explode(',', $term->nodes_ids));
			}

			self::$cache_ar_terms[$key] = $terms;
		}

		$nid = $target->nid;

		if ($vocabulary->is_multiple || $vocabulary->is_tags)
		{
			$rc = array();

			foreach (self::$cache_ar_terms[$key] as $term)
			{
				if (!isset($term->nodes_ids[$nid]))
				{
					continue;
				}

				$rc[] = $use_slug ? $term->termslug : $term;
			}

			$event->value = $rc;
			$event->stop();
		}
		else
		{
			foreach (self::$cache_ar_terms[$key] as $term)
			{
				if (!isset($term->nodes_ids[$nid]))
				{
					continue;
				}

				$event->value = $use_slug ? $term->termslug : $term;
				$event->stop();

				return;
			}
		}
	}

	public static function on_nodes_editblock_alter_children(Event $event, Modules\Nodes\EditBlock $block)
	{
		global $core;

		$document = $core->document;

		$document->css->add('public/support.css');
		$document->js->add('public/support.js');

		$vocabularies = $core->models['taxonomy.vocabulary']
		->joins('INNER JOIN {self}__scopes USING(vid)')
		->where('constructor = ? AND (siteid = 0 OR siteid = ?)', (string) $event->module, $core->site_id)
		->order('weight')
		->all;

		// TODO-20101104: use Brickrouge\Form::VALUES instead of setting the 'values' of the elements.
		// -> because 'properties' are ignored, and that's bad.

		$terms_model = $core->models['taxonomy.terms'];
		$nodes_model = $core->models['taxonomy.terms/nodes'];

		$nid = $event->key;
		$identifier_base = 'vocabulary[vid]';
		$children = &$event->children;

		foreach ($vocabularies as $vocabulary)
		{
			$vid = $vocabulary->vid;;

			$identifier = $identifier_base . '[' . $vid . ']';

			if ($vocabulary->is_multiple)
			{
				$options = $terms_model->select('term, count(nid)')
				->joins('inner join {self}__nodes using(vtid)')
				->filter_by_vid($vid)
				->group('term')->order('term')->pairs;

				$value = $nodes_model->select('term')
				->filter_by_vid_and_nid($vid, $nid)
				->order('term')
				->all(\PDO::FETCH_COLUMN);
				$value = implode(', ', $value);

				$label = $vocabulary->vocabulary;

				$children[] = new Element
				(
					'div', array
					(
						Form::LABEL => $label,

						Element::GROUP => 'organize',
						Element::WEIGHT => 100,

						Element::CHILDREN => array
						(
							new Text
							(
								array
								(
									'value' => $value,
									'name' => $identifier
								)
							),

							new \WdCloudElement
							(
								'ul', array
								(
									Element::OPTIONS => $options,
									'class' => 'cloud'
								)
							)
						),

						'class' => 'taxonomy-tags combo'
					)
				);
			}
			else
			{
				$options = $terms_model->select('term.vtid, term')->filter_by_vid($vid)->order('term')->pairs;

				if (!$options)
				{
					//continue;
				}

				$value = $nodes_model->select('term_node.vtid')->filter_by_vid_and_nid($vid, $nid)->order('term')->rc;

				$edit_url = $core->site->path . '/admin/taxonomy.vocabulary/' . $vocabulary->vid . '/edit';

				$children[$identifier] = new Element
				(
					'select', array
					(
						Form::LABEL => $vocabulary->vocabulary,
						Element::GROUP => 'organize',
						Element::OPTIONS => array(null => '') + $options,
						Element::REQUIRED => $vocabulary->is_required,
						Element::INLINE_HELP => '<a href="' . $edit_url . '">' . t('Edit the vocabulary <q>!vocabulary</q>', array('!vocabulary' => $vocabulary->vocabulary)) . '</a>.',

						'value' => $value
					)
				);
			}
		}

		// FIXME: There is no class to create a _tags_ element. They are created using a collection
		// of objects in a div, so the key is a numeric, not an identifier.

		$event->attributes[Element::GROUPS]['organize'] = array
		(
			'title' => 'Organization',
			'weight' => 500
		);
	}

	public static function on_node_save(Event $event, \ICanBoogie\Modules\Nodes\SaveOperation $sender)
	{
		global $core;

		$name = 'vocabulary';
		$params = $event->request->params;

		if (empty($params[$name]))
		{
			return;
		}

		$nid = $event->rc['key'];
		$vocabularies = $params[$name]['vid'];

		#
		# on supprime toutes les liaisons pour cette node
		#

		$vocabulary_model = $core->models['taxonomy.vocabulary'];
		$terms_model = $core->models['taxonomy.terms'];
		$nodes_model = $core->models['taxonomy.terms/nodes'];

		$nodes_model->where('nid = ?', $nid)->delete();

		#
		# on crée maintenant les nouvelles liaisons
		#

		foreach ($vocabularies as $vid => $values)
		{
			if (!$values)
			{
				continue;
			}

			$vocabulary = $vocabulary_model[$vid];

			if ($vocabulary->is_tags)
			{
				#
				# because tags are provided as a string with coma separated terms,
				# we need to get/created terms id before we can update the links between
				# terms and nodes
				#

				$terms = explode(',', $values);
				$terms = array_map('trim', $terms);

				$values = array();

				foreach ($terms as $term)
				{
					$vtid = $terms_model->select('vtid')->where('vid = ? and term = ?', $vid, $term)->rc;

					// FIXME-20090127: only users with 'create tags' permissions should be allowed to create tags

					if (!$vtid)
					{
						$vtid = $terms_model->save
						(
							array
							(
								'vid' => $vid,
								'term' => $term
							)
						);
					}

					$values[] = $vtid;
				}
			}

			foreach ((array) $values as $vtid)
			{
				$nodes_model->insert
				(
					array
					(
						'vtid' => $vtid,
						'nid' => $nid
					),

					array
					(
						'ignore' => true
					)
				);
			}
		}
	}

	public static function on_collect_views(ViewsCollection\CollectEvent $event, ViewsCollection $target)
	{
		global $core;

		$vocabulary = $core->models['taxonomy.vocabulary']->all;
		$collection = &$event->collection;

		foreach ($vocabulary as $v)
		{
			$scope = $v->scope;
			$vocabulary_name = $v->vocabulary;
			$vocabulary_slug = $v->vocabularyslug;

			foreach ($scope as $constructor)
			{
				$view_home = $constructor . '/home';
				$view_home = isset($collection[$view_home]) ? $collection[$view_home] : null;

				$view_list = $constructor . '/list';
				$view_list = isset($collection[$view_list]) ? $collection[$view_list] : null;

				if ($view_home)
				{
					$collection["$constructor/vocabulary/$vocabulary_slug/vocabulary-home"] = array
					(
						'title' => 'Home for vocabulary %name',
						'title args' => array('name' => $v->vocabulary),
						'taxonomy vocabulary' => $v
					)

					+ $view_home;
				}

				if ($view_list)
				{
					$collection["$constructor/vocabulary/$vocabulary_slug/list"] = array
					(
						'title' => 'Records list, in vocabulary %vocabulary and a term',
						'title args' => array('vocabulary' => $vocabulary_name),
						'taxonomy vocabulary' => $v
					)

					+ $view_list;
				}

				foreach ($v->terms as $term)
				{
					$term_name = $term->term;
					$term_slug = $term->termslug;

					if ($view_home)
					{
						$collection["$constructor/vocabulary/$vocabulary_slug/$term_slug/home"] = array
						(
							'title' => 'Records home, in vocabulary %vocabulary and term %term',
							'title args' => array('vocabulary' => $vocabulary_name, 'term' => $term_name),
							'taxonomy vocabulary' => $v,
							'taxonomy term' => $term,
						)

						+ $view_home;
					}

					if ($view_list)
					{
						$collection["$constructor/vocabulary/$vocabulary_slug/$term_slug/list"] = array
						(
							'title' => 'Records list, in vocabulary %vocabulary and term %term',
							'title args' => array('vocabulary' => $vocabulary_name, 'term' => $term_name),
							'taxonomy vocabulary' => $v,
							'taxonomy term' => $term
						)

						+ $view_list;
					}
				}
			}
		}
	}

	public static function on_alter_provider_query(Event $event, Provider $provider)
	{
		global $core;

// 		var_dump($event->view);

		$options = $event->view->options;

		if (isset($options['taxonomy vocabulary']) && isset($options['taxonomy term']))
		{
			return self::for_vocabulary_and_term($event, $provider, $options, $options['taxonomy vocabulary'], $options['taxonomy term']);
		}

		if (empty($event->view->options['taxonomy vocabulary']))
		{
			return;
		}

		$vocabulary = $event->view->options['taxonomy vocabulary'];
		$condition = $vocabulary->vocabularyslug . 'slug';

		if (empty($event->conditions[$condition]))
		{
			# show all by category ?

			$event->view->range['limit'] = null; // cancel limit TODO-20120403: this should be improved.

			\ICanBoogie\Events::attach('Icybee\Modules\Views\ActiveRecordProvider::alter_result', array(__CLASS__, 'on_alter_provider_result'));

			return;
		}

		$condition_value = $event->conditions[$condition];

		$term = $core->models['taxonomy.terms']->where('vid = ? AND termslug = ?', array($vocabulary->vid, $condition_value))->order('term.weight')->one;

		$event->query->where('nid IN (SELECT nid FROM {prefix}taxonomy_terms
		INNER JOIN {prefix}taxonomy_terms__nodes USING(vtid) WHERE vtid = ?)', $term ? $term->vtid : 0);

		#

		global $core;

		$page = isset($core->request->context->page) ? $core->request->context->page : null;

		if ($page && $term)
		{
			$page->title = \ICanBoogie\format($page->title, array(':term' => $term->term));
		}
	}

	public static function on_alter_provider_result(\Icybee\Modules\Views\ActiveRecordProvider\AlterResultEvent $event, \Icybee\Modules\Views\ActiveRecordProvider $provider)
	{
		global $core;

		$vocabulary = $event->view->options['taxonomy vocabulary'];

		$ids = '';
		$records_by_id = array();

		foreach ($event->result as $record)
		{
			if (!($record instanceof \ICanBoogie\ActiveRecord\Node))
			{
				/*
				 * we return them as [ term: [], nodes: []]
				 *
				 * check double event ?
				 *
				 * http://demo.icybee.localhost/articles/category/
				 *
				trigger_error(\ICanBoogie\format('Expected instance of <q>ICanBoogie\ActiveRecord\Node</q> given: \1', array($record)));

				var_dump($event); exit;
				*/

				continue;
			}

			$nid = $record->nid;
			$ids .= ',' . $nid;
			$records_by_id[$nid] = $record;
		}

		if (!$ids)
		{
			return;
		}

		$ids = substr($ids, 1);

		/*
		$ids_by_names = $core->models['taxonomy.terms/nodes']
		->joins(':nodes')
		->select('term, nid')
		->order('term.weight, term.term')
		->where('vid = ? AND nid IN(' . $ids . ')', $vocabulary->vid)
		->all(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN);

		var_dump($ids_by_names);

		$result = array();

		foreach ($ids_by_names as $name => $ids)
		{
			$ids = array_flip($ids);

			foreach ($event->result as $record)
			{
				if (isset($ids[$record->nid]))
				{
					$result[$name][] = $record;
				}
			}
		}

		$event->result = $result;
		*/

		$ids_by_vtid = $core->models['taxonomy.terms/nodes']
		->joins(':nodes')
		->select('vtid, nid')
		->order('term.weight, term.term')
		->where('vid = ? AND nid IN(' . $ids . ')', $vocabulary->vid)
		->all(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN);

		$terms = $core->models['taxonomy.terms']->find(array_keys($ids_by_vtid));

		$result = array();

		foreach ($ids_by_vtid as $vtid => $ids)
		{
			$result[$vtid]['term'] = $terms[$vtid];
			$result[$vtid]['nodes'] = array_intersect_key($records_by_id, array_combine($ids, $ids));
		}

		$event->result = $result;
	}

	private static function for_vocabulary_and_term(Event $event, Provider $provider, $options, ActiveRecord\Taxonomy\Vocabulary $vocabulary, ActiveRecord\Taxonomy\Term $term)
	{
		$event->query->where('nid IN (SELECT nid FROM {prefix}taxonomy_terms
		INNER JOIN {prefix}taxonomy_terms__nodes USING(vtid) WHERE vtid = ?)', $term ? $term->vtid : 0);



		/*
		\ICanBoogie\Events::attach
		(
			'ICanBoogie\ActiveRecord\Page::render_title', function()
			{
				var_dump(func_get_args());
			}
		);
		*/
	}

	public static function before_breadcrumb_render_inner_html(\ICanBoogie\Modules\Pages\BreadcrumbElement\BeforeRenderInnerHTMLEvent $event, \ICanBoogie\Modules\Pages\BreadcrumbElement $target)
	{
		foreach ($event->slices as &$slice)
		{
			if (strpos($slice['label'], ':term') === false || empty($event->page->node)) continue;

			$slice['label'] = \ICanBoogie\format($slice['label'], array('term' => (string) $event->page->node->category));
		}
	}
}