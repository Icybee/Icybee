<?php

/*
 * This file is part of the Icybee package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function wd_entities($str, $charset=ICanBoogie\CHARSET)
{
	return htmlspecialchars($str, ENT_COMPAT, $charset);
}

function wd_entities_all($str, $charset=ICanBoogie\CHARSET)
{
	return htmlentities($str, ENT_COMPAT, $charset);
}

// http://www.ranks.nl/resources/stopwords.html

function wd_strip_stopwords($str, $stopwords=null)
{
	$stopwords = 'alors au aucuns aussi autre avant avec avoir à bon car ce cela ces ceux chaque
ci comme comment dans de des dedans dehors depuis deux devrait doit donc dos droite du début elle
elles en encore essai est et eu fait faites fois font force haut hors ici il ils je juste la le
les leur là ma maintenant mais mes mine moins mon mot même ni nommés notre nous nouveaux ou où
par parce parole pas personnes peut peu pièce plupart pour pourquoi quand que quel quelle quelles
quels qui sa sans ses seulement si sien son sont sous soyez sujet sur ta tandis tellement tels
tes ton tous tout trop très tu valeur voie voient vont votre vous vu ça étaient état étions été
être';

	$stopwords = explode(' ', preg_replace('#\s+#', ' ', $stopwords));

	$patterns = array();

	foreach ($stopwords as $word)
	{
		$patterns[] = '# ' . preg_quote($word) . ' #i';
	}

	return preg_replace($patterns, ' ', $str);
}

function wd_slugize($str, $stopwords=null)
{
	$str = wd_strip_stopwords($str);

	return trim(substr(wd_normalize($str), 0, 80), '-');
}

function wd_spamScore($body, $url, $author, $words=array(), $starters=array())
{
	#
	# score >= 1 - The message doesn't look like spam
	# score == 0 - The message should be put to moderation
	# score < 10 - The message is most certainly spam
	#

	$score = 0;

	#
	# put our body in lower case for checking
	#

	$body = strtolower($body);

	#
	# how many links are in the body ?
	#

	$n = max
	(
		array
		(
			substr_count($body, 'http://'),
			substr_count($body, 'href'),
			substr_count($body, 'ftp')
		)
	);

	if ($n > 2)
	{
		#
		# more than 2 : -1 point per link
		#

		$score -= $n;
	}
	else
	{
		#
		# 2 or less : +2 points
		#

		$score += 2;
	}

	#
	# Keyword search
	#

	$words = array_merge
	(
		$words, array
		(
			'levitra', 'viagra', 'casino', 'porn', 'sex', 'tape'
		)
	);

	foreach ($words as $word)
	{
		$n = substr_count($body, $word);

		if (!$n)
		{
			continue;
		}

		$score -= $n;
	}

	#
	# now remove links
	#

	# html style: <a> <a/>

	$body = preg_replace('#\<a\s.+\<\/a\>#', NULL, $body);

	# bb style: [url] [/url]

	$body = preg_replace('#\[url.+\/url\]#', NULL, $body);

	# remaining addresses: http://

	$body = preg_replace('#http://[^\s]+#', NULL, $body);

	#
	# how long is the body ?
	#

	$l = strlen($body);

	if ($l > 20 && $n = 0)
	{
		#
		# More than 20 characters and there's no links : +2 points
		#

		$score += 2;
	}
	else if ($l < 20)
	{
		#
		# Less than 20 characters : -1 point
		#

		$score--;
	}

	#
	# URL length
	#

	if (strlen($url) > 32)
	{
		$score--;
	}

	#
	# Body starts with...
	#

	$starters += array
	(
		'interesting', 'sorry', 'nice', 'cool', 'hi'
	);

	foreach ($starters as $word)
	{
		$pos = strpos($body, $word . ' ');

		if ($pos === false)
		{
			continue;
		}

		if ($pos > 10)
		{
			continue;
		}

		$score -= 10;

		break;
	}

	#
	# Author name has 'http://' in it
	#

	if (strpos($author, 'http://'))
	{
		$score -= 2;
	}

	#
	# How many different words are used
	#

	$count = str_word_count($body);

	if ($count < 10)
	{
		$score -= 5;
	}

	return $score;

	# TODO:
	#
	# Number of previous comments from email
	#
	# 	-> Approved comments : +1 per comment
	#	-> Marked as spam : -1 per comment
	#
	# Body used in previous comment
	#
}
