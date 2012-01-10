<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Module;

use ICanBoogie\ActiveRecord\Comment;
use ICanBoogie\ActiveRecord\Query;
use BrickRouge\Element;
use BrickRouge\Form;
use BrickRouge\Text;

use Icybee\Manager;

class Comments extends \Icybee\Module
{
	protected function __get_views()
	{
		$assets = array('css' => $this->descriptor[self::T_PATH] . 'public/page.css');

		return array
		(
			'list' => array
			(
				'title' => "Comments associated to a node",
				'assets' => $assets,
				'provider' => true,
				'renders' => \Icybee\Views\View::RENDERS_MANY
			),

			'submit' => array
			(
				'title' => "Comment submit form",
				'assets' => $assets,
				'renders' => \Icybee\Views\View::RENDERS_OTHER
			)
		);
	}

	/*
	static $notifies_response = array
	(
		'subject' => 'Notification de réponse au billet : #{@node.title}',
		'template' => 'Bonjour,

Vous recevez cet email parce que vous surveillez le billet "#{@node.title}" sur <nom_du_site>.
Ce billet a reçu une réponse depuis votre dernière visite. Vous pouvez utiliser le lien suivant
pour voir les réponses qui ont été faites :

#{@absolute_url}

Aucune autre notification ne vous sera envoyée.

À bientôt sur <url_du_site>',
		'from' => 'VotreSite <no-reply@votre_site.com>'
	);
	*/

	protected function block_edit(array $properties, $permission)
	{
		return array
		(
			Element::CHILDREN => array
			(
				Comment::AUTHOR => new Text
				(
					array
					(
						Form::LABEL => 'Author',
						Element::REQUIRED => true
					)
				),

				Comment::AUTHOR_EMAIL => new Text
				(
					array
					(
						Form::LABEL => 'E-mail',
						Element::REQUIRED => true
					)
				),

				Comment::AUTHOR_URL => new Text
				(
					array
					(
						Form::LABEL => 'URL'
					)
				),

				Comment::AUTHOR_IP => new Text
				(
					array
					(
						Form::LABEL => 'Adresse IP',
						Element::DESCRIPTION => "Status spam: <em>en cours de vérification</em>.",

						'disabled' => true
					)
				),

				Comment::CONTENTS => new Element
				(
					'textarea', array
					(
						Form::LABEL => 'Message',
						Element::REQUIRED => true,

						'rows' => 10
					)
				),

				Comment::NOTIFY => new Element
				(
					Element::TYPE_RADIO_GROUP, array
					(
						Form::LABEL => 'Notification',
						Element::DEFAULT_VALUE => 'no',
						Element::REQUIRED => true,
						Element::OPTIONS => array
						(
							'yes' => 'Bien sûr !',
							'author' => "Seulement si c'est l'auteur du billet qui répond",
							'no' => 'Pas la peine, je viens tous les jours',
							'done' => 'Notification envoyée'
						),

						Element::DESCRIPTION => (($properties[Comment::NOTIFY] == 'done') ? "Un
						message de notification a été envoyé." : null),

						'class' => 'list'
					)
				),

				Comment::STATUS => new Element
				(
					'select', array
					(
						Form::LABEL => 'Status',
						Element::REQUIRED => true,
						Element::OPTIONS => array
						(
							null => '',
							'pending' => 'Pending',
							'approved' => 'Aprouvé',
							'spam' => 'Spam'
						)
					)
				)
			)
		);
	}

	protected function block_manage()
	{
		return new Manager\Comments
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array
				(
					'created', 'author', /*'score',*/ 'nid'
				),

				Manager::T_ORDER_BY => array('created', 'desc'),
				Manager\Comments::T_LIST_SPAM => false
			)
		);
	}

	protected function block_manage_spam()
	{
		return new Manager\Comments
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array
				(
					'created', 'author', 'score', 'nid'
				),

				Manager::T_ORDER_BY => array('created', 'desc'),

				Manager\Comments::T_LIST_SPAM => true
			)
		);
	}

	protected function block_config()
	{
		global $core;

		// TODO-20101101: move this to operation `config`

		$keywords = $core->registry[$this->flat_id . '.spam.keywords'];
		$keywords = preg_split('#[\s,]+#', $keywords, 0, PREG_SPLIT_NO_EMPTY);

		sort($keywords);

		$keywords = implode(', ', $keywords);

		return array
		(
			Form::VALUES => array
			(
				"global[$this->flat_id.spam.keywords]" => $keywords
			),

			Element::GROUPS => array
			(
				'primary' => array
				(
					'title' => 'Général',
					'class' => 'form-section flat'
				),

				'response' => array
				(
					'title' => "Message de notification à l'auteur lors d'une réponse",
					'class' => 'form-section flat'
				),

				'spam' => array
				(
					'title' => 'Paramètres du filtre anti-spam',
					'class' => 'form-section flat',
					'description' => "Les paramètres du filtre anti-spam s'appliquent à tous les
					sites."
				)
			),

			Element::CHILDREN => array
			(
				"local[$this->flat_id.form_id]" => new \WdFormSelectorElement
				(
					'select', array
					(
						Form::LABEL => 'Formulaire',
						Element::GROUP => 'primary',
						Element::REQUIRED => true,
						Element::DESCRIPTION => "Il s'agit du formulaire à utiliser pour la
						saisie des commentaires."
					)
				),

				"local[$this->flat_id.delay]" => new Text
				(
					array
					(
						Form::LABEL => 'Intervale entre deux commentaires',
						Element::LABEL => 'minutes',
						Element::DEFAULT_VALUE => 3,

						'size' => 3,
						'style' => 'text-align: right'
					)
				),

				"local[$this->flat_id.default_status]" => new Element
				(
					'select', array
					(
						Form::LABEL => 'Status par défaut',
						Element::OPTIONS => array
						(
							'pending' => 'Pending',
							'approved' => 'Approuvé'
						),
						Element::DESCRIPTION => "Il s'agit du status par défaut pour les nouveaux
						commentaires."
					)
				),

				"global[$this->flat_id.spam.urls]" => new Element
				(
					'textarea', array
					(
						Form::LABEL => 'URLs',
						Element::GROUP => 'spam',
						'rows' => 5
					)
				),

				"global[$this->flat_id.spam.keywords]" => new Element
				(
					'textarea', array
					(
						Form::LABEL => 'Mots clés',
						Element::GROUP => 'spam',
						'rows' => 5
					)
				)
			)
		);
	}

	protected static $spam_score_keywords;
	protected static $forbidden_urls;

	static public function score_spam($contents, $url, $author)
	{
		global $core;

		if (self::$spam_score_keywords === null)
		{
			$keywords = $core->registry['comments.spam.keywords'];

			if ($keywords)
			{
				$keywords = preg_split('#[\s,]+#', $keywords, 0, PREG_SPLIT_NO_EMPTY);
			}
			else
			{
				$keywords = array();
			}

			self::$spam_score_keywords = $keywords;
		}

		$score = wd_spamScore($contents, $url, $author, self::$spam_score_keywords);

		#
		# additionnal contents restrictions
		#

		$score -= substr_count($contents, '[url=');

		#
		# additionnal author restrictions
		#

		if ($author{0} == '#')
		{
			$score -= 5;
		}

		if (in_array($author, self::$spam_score_keywords))
		{
			$score -= 1;
		}

		#
		# additionnal url restrictions
		#

		if (self::$forbidden_urls === null)
		{
			$forbidden_urls = $core->registry['comments.spam.urls'];

			if ($forbidden_urls)
			{
				$forbidden_urls = preg_split('#[\s,]+#', $forbidden_urls, 0, PREG_SPLIT_NO_EMPTY);
			}

			self::$forbidden_urls = $forbidden_urls;
		}

		if (self::$forbidden_urls)
		{
			foreach (self::$forbidden_urls as $forbidden)
			{
				if (strpos($contents . $url, $forbidden) !== false)
				{
					$score -= 5;
				}
			}
		}

		return $score;
	}

	protected function provide_view_list(Query $query, \WdPatron $patron)
	{
		global $page;

		$target = $page ? $page->node : $page;

		$comments = $this->model->where('nid = ? AND status = "approved"', $target->nid)->order('created')->all;

		$patron->context['self']['count'] = t(':count comments', array(':count' => count($comments)));

		return $comments;
	}
}