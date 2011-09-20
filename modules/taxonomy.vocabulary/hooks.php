<?php

namespace ICanBoogie\Hooks\Taxonomy;

use ICanBoogie\ActiveRecord;
use ICanBoogie\Event;
use ICanBoogie\Module;
use ICanBoogie\Operation;

use BrickRouge;
use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Text;

class Vocabulary
{
	protected static $cache_ar_vocabularies = array();
	protected static $cache_ar_terms = array();

	public static function get_term(Event $event, ActiveRecord\Node $sender)
	{
		global $core;

		$constructor = $sender->constructor;
		$property = $vocabularyslug = $event->property;
		$siteid = $sender->siteid;

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
			->where('constructor = ? AND vocabularyslug = ? AND (siteid = 0 OR siteid = ?)', (string) $constructor, $vocabularyslug, $sender->siteid)
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

		$nid = $sender->nid;

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

	public static function alter_block_edit(Event $event, Module $sender)
	{
		global $core;

		$document = $core->document;

		$document->css->add('public/support.css');
		$document->js->add('public/support.js');

		$vocabularies = $core->models['taxonomy.vocabulary']
		->joins('INNER JOIN {self}__scopes USING(vid)')
		->where('constructor = ? AND (siteid = 0 OR siteid = ?)', (string) $sender, $core->site_id)
		->order('weight')
		->all;

		// TODO-20101104: use BrickRouge\Form::T_VALUES instead of setting the 'values' of the elements.
		// -> because 'properties' are ignored, and that's bad.

		$terms_model = $core->models['taxonomy.terms'];
		$nodes_model = $core->models['taxonomy.terms/nodes'];

		$nid = $event->key;
		$identifier_base = 'vocabulary[vid]';
		$children = array();

		foreach ($vocabularies as $vocabulary)
		{
			$vid = $vocabulary->vid;;

			$identifier = $identifier_base . '[' . $vid . ']';

			if ($vocabulary->is_multiple)
			{
				$options = $terms_model->select('term, count(nid)')
				->joins('inner join {self}__nodes using(vtid)')
				->find_by_vid($vid)
				->group('term')->order('term')->pairs;

				$value = $nodes_model->select('term')->find_by_vid_and_nid($vid, $nid)->order('term')->all(\PDO::FETCH_COLUMN);
				$value = implode(', ', $value);

				$label = $vocabulary->vocabulary;

				$children[] = new Element
				(
					'div', array
					(
						Form::T_LABEL => $label,

						Element::T_GROUP => 'organize',
						Element::T_WEIGHT => 100,

						Element::T_CHILDREN => array
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
									Element::T_OPTIONS => $options,
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
				$options = $terms_model->select('term.vtid, term')->find_by_vid($vid)->order('term')->pairs;

				if (!$options)
				{
					//continue;
				}

				$value = $nodes_model->select('node.vtid')->find_by_vid_and_nid($vid, $nid)->order('term')->rc;

				$edit_url = $core->site->path . '/admin/taxonomy.vocabulary/' . $vocabulary->vid . '/edit';

				$children[$identifier] = new Element
				(
					'select', array
					(
						Form::T_LABEL => $vocabulary->vocabulary,
						Element::T_GROUP => 'organize',
						Element::T_OPTIONS => array(null => '') + $options,
						Element::T_REQUIRED => $vocabulary->is_required,
						Element::T_DESCRIPTION => '<a href="' . $edit_url . '">' . t('Edit the vocabulary <q>!vocabulary</q>', array('!vocabulary' => $vocabulary->vocabulary)) . '</a>.',

						'value' => $value
					)
				);
			}
		}

		// FIXME: There is no class to create a _tags_ element. They are created using a collection
		// of objects in a div, so the key is a numeric, not an identifier.

		$event->tags = wd_array_merge_recursive
		(
			$event->tags, array
			(
				Element::T_GROUPS => array
				(
					'organize' => array
					(
						'title' => '.organize',
						'class' => 'form-section flat',
						'weight' => 500
					)
				),

				Element::T_CHILDREN => $children
			)
		);
	}

	public static function on_node_save(Event $event, Operation\Nodes\Save $sender)
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
}