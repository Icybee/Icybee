<?php

namespace Icybee\Modules\Taxonomy\Vocabulary;

use ICanBoogie\Event;
use ICanBoogie\I18n;

use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

use Icybee\Modules\Views\ActiveRecordProvider;
use Icybee\Modules\Views\Collection as ViewsCollection;
use Icybee\Modules\Views\Provider;

class Hooks
{
	static private $cache_vocabularies = array();
	static private $cache_record_terms = array();
	static private $cache_record_properties = array();

	static public function get_term(\ICanBoogie\Object\PropertyEvent $event, \Icybee\Modules\Nodes\Node $target)
	{
		global $core;

		$property = $event->property;
		$cache_record_properties_key = spl_object_hash($target) . '_' . $property;

		if (isset(self::$cache_record_properties[$cache_record_properties_key]))
		{
			$event->value = self::$cache_record_properties[$cache_record_properties_key];
			$event->stop();

			return;
		}

		$constructor = $target->constructor;
		$siteid = $target->siteid;
		$nid = $target->nid;

		$use_slug = false;
		$vocabularyslug = $property;

		if (substr($property, -4, 4) === 'slug')
		{
			$use_slug = true;
			$vocabularyslug = substr($property, 0, -4);
		}

		$cache_key = $siteid . '>' . $constructor . '>' . $vocabularyslug;
		$cache_record_terms_key = $cache_key . '>' . $nid;

		if (!isset(self::$cache_record_terms[$cache_record_terms_key]))
		{
			#
			# vocabulary for this constructor on this website
			#

			if (!isset(self::$cache_vocabularies[$cache_key]))
			{
				self::$cache_vocabularies[$cache_key] = $core->models['taxonomy.vocabulary']
				->joins(':taxonomy.vocabulary/scopes')
				->where('siteid = 0 OR siteid = ?', $target->siteid)
				->filter_by_constructor((string) $constructor)
				->filter_by_vocabularyslug($vocabularyslug)
				->order('siteid DESC')
				->one;
			}

			$vocabulary = self::$cache_vocabularies[$cache_key];

			if (!$vocabulary)
			{
				return;
			}

			if ($vocabulary->is_required)
			{
				$event->value = 'uncategorized';
			}

			$terms = $vocabulary->terms;
			$rc = null;

			if ($vocabulary->is_multiple || $vocabulary->is_tags)
			{
				foreach ($terms as $term)
				{
					if (empty($term->nodes_keys[$nid]))
					{
						continue;
					}

					$rc[] = $term;
				}
			}
			else
			{
				foreach ($terms as $term)
				{
					if (empty($term->nodes_keys[$nid]))
					{
						continue;
					}

					$rc = $term;

					break;
				}
			}

			self::$cache_record_terms[$cache_record_terms_key] = $rc === null ? false : $rc;
		}

		$rc = self::$cache_record_terms[$cache_record_terms_key];

		if ($rc === false)
		{
			return;
		}

		if ($use_slug)
		{
			if (is_array($rc))
			{
				$terms = $rc;
				$rc = array();

				foreach ($terms as $term)
				{
					$rc[] = $term->termslug;
				}
			}
			else
			{
				$rc = $rc->termslug;
			}
		}

		self::$cache_record_properties[$cache_record_properties_key] = $rc;

		/*
		$cache = &self::$cache_record_properties;

		#
		# now that we have the value for the property we can set a prototype method to provide the
		# value without the events overhead.
		#

		$target->prototype['volatile_get_' . $property] = function(\Icybee\Modules\Nodes\Node $target) use($property, &$cache)
		{
			$cache_record_properties_key = spl_object_hash($target) . '_' . $property;

			var_dump($cache);

			return $cache[$cache_record_properties_key];
		};
		*/

		$target->$property = $rc;

		$event->value = $rc;
		$event->stop();
	}

	static public function on_nodes_editblock_alter_children(Event $event, \Icybee\Modules\Nodes\EditBlock $block)
	{
		global $core;

		$document = $core->document;

		$document->css->add(DIR . 'public/support.css');
		$document->js->add(DIR . 'public/support.js');

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
						Element::INLINE_HELP => '<a href="' . $edit_url . '">' . I18n\t('Edit the vocabulary <q>!vocabulary</q>', array('!vocabulary' => $vocabulary->vocabulary)) . '</a>.',

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

	static public function on_node_save(\ICanBoogie\Operation\ProcessEvent $event, \Icybee\Modules\Nodes\SaveOperation $target)
	{
		global $core;

		$name = 'vocabulary';
		$request = $event->request;
		$vocabularies = $request[$name];

		if (!$vocabularies)
		{
			return;
		}

		$nid = $event->rc['key'];
		$vocabularies = $vocabularies['vid'];

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

	static public function on_collect_views(ViewsCollection\CollectEvent $event, ViewsCollection $target)
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

	static public function on_alter_provider_query(\Icybee\Modules\Views\ActiveRecordProvider\AlterQueryEvent $event, \Icybee\Modules\Views\ActiveRecordProvider $provider)
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

		#
		# FIXME-20121226: It has to be known that the conditions is `<vocabularyslug>slug`.
		#
		# is condition is required by "in vocabulary and a term", but we don't check that, which
		# can cause problems when the pattern of the page is incorrect e.g. "tagslug" instead of
		# "tagsslug"
		#

		if (empty($event->conditions[$condition]))
		{
			# show all by category ?

			$event->view->range['limit'] = null; // cancel limit TODO-20120403: this should be improved.

			$core->events->attach(array(__CLASS__, 'on_alter_provider_result'));

			return;
		}

		$condition_value = $event->conditions[$condition];

		$term = $core->models['taxonomy.terms']->where('vid = ? AND termslug = ?', array($vocabulary->vid, $condition_value))->order('term.weight')->one;

		$core->events->attach(function(ActiveRecordProvider\AlterContextEvent $event, ActiveRecordProvider $target) use($term) {

			$event->context['term'] = $term;

		});

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

	static public function on_alter_provider_result(\Icybee\Modules\Views\ActiveRecordProvider\AlterResultEvent $event, \Icybee\Modules\Views\ActiveRecordProvider $provider)
	{
		global $core;

		$vocabulary = $event->view->options['taxonomy vocabulary'];

		$ids = '';
		$records_by_id = array();

		foreach ($event->result as $record)
		{
			if (!($record instanceof \Icybee\Modules\Nodes\Node))
			{
				/*
				 * we return them as [ term: [], nodes: []]
				 *
				 * check double event ?
				 *
				 * http://demo.icybee.localhost/articles/category/
				 *
				trigger_error(\ICanBoogie\format('Expected instance of <q>Icybee\Modules\Nodes\Node</q> given: \1', array($record)));

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

	static private function for_vocabulary_and_term(Event $event, Provider $provider, $options, \Icybee\Modules\Taxonomy\Vocabulary\Vocabulary $vocabulary, \Icybee\Modules\Taxonomy\Terms\Term $term)
	{
		$event->query->where('nid IN (SELECT nid FROM {prefix}taxonomy_terms
		INNER JOIN {prefix}taxonomy_terms__nodes USING(vtid) WHERE vtid = ?)', $term ? $term->vtid : 0);



		/*
		$core->events->attach
		(
			'Icybee\Modules\Pages\Page::render_title', function()
			{
				var_dump(func_get_args());
			}
		);
		*/
	}

	static public function before_breadcrumb_render_inner_html(\Icybee\Modules\Pages\BreadcrumbElement\BeforeRenderInnerHTMLEvent $event, \Icybee\Modules\Pages\BreadcrumbElement $target)
	{
		foreach ($event->slices as &$slice)
		{
			if (strpos($slice['label'], ':term') === false || empty($event->page->node)) continue;

			$slice['label'] = \ICanBoogie\format($slice['label'], array('term' => (string) $event->page->node->category));
		}
	}
}