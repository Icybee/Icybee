<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ICanBoogie\Modules\Comments;

use ICanBoogie\ActiveRecord\Comment;
use ICanBoogie\ActiveRecord\Query;
use Brickrouge\Element;
use Brickrouge\Form;
use Brickrouge\Text;

class Module extends \Icybee\Module
{
	protected function __get_views()
	{
		$assets = array('css' => __DIR__ . '/public/page.css');

		return array
		(
			'list' => array
			(
				'title' => "Comments associated to a node",
				'assets' => $assets,
				'provider' => 'ICanBoogie\Modules\Comments\Provider',
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

	protected function block_manage()
	{
		return new Manager
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array
				(
					'comment', 'status', 'author', /*'score',*/ 'nid', 'created'
				),

				Manager::T_ORDER_BY => array('created', 'desc'),
// 				Manager::T_LIST_SPAM => false
			)
		);
	}

	protected function block_manage_spam()
	{
		return new Manager
		(
			$this, array
			(
				Manager::T_COLUMNS_ORDER => array
				(
					'created', 'author', 'score', 'nid'
				),

				Manager::T_ORDER_BY => array('created', 'desc'),

				Manager::T_LIST_SPAM => true
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