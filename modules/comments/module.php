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
	static public function score_spam($contents, $url, $author)
	{
		global $core;

		$score = wd_spamScore($contents, $url, $author);

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

		return $score;
	}

	protected function get_views()
	{
		$assets = array('css' => __DIR__ . '/public/page.css');

		return array
		(
			'list' => array
			(
				'title' => "Comments associated to a node",
				'assets' => $assets,
				'provider' => 'ICanBoogie\Modules\Comments\Provider',
				'renders' => \Icybee\Modules\Views\View::RENDERS_MANY
			),

			'submit' => array
			(
				'title' => "Comment submit form",
				'assets' => $assets,
				'renders' => \Icybee\Modules\Views\View::RENDERS_OTHER
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
}