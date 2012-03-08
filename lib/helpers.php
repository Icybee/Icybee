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
