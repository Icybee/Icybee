<?php

use Brickrouge\Pager;
use ICanBoogie\Module;

if (!defined('PREG_CLASS_SEARCH_EXCLUDE'))
{
	/**
	 * Matches Unicode character classes to exclude from the search index.
	 *
	 * See: http://www.unicode.org/Public/UNIDATA/UCD.html#General_Category_Values
	 *
	 * The index only contains the following character classes:
	 * Lu     Letter, Uppercase
	 * Ll     Letter, Lowercase
	 * Lt     Letter, Titlecase
	 * Lo     Letter, Other
	 * Nd     Number, Decimal Digit
	 * No     Number, Other
	 */
	define('PREG_CLASS_SEARCH_EXCLUDE',
	'\x{0}-\x{2f}\x{3a}-\x{40}\x{5b}-\x{60}\x{7b}-\x{bf}\x{d7}\x{f7}\x{2b0}-'.
	'\x{385}\x{387}\x{3f6}\x{482}-\x{489}\x{559}-\x{55f}\x{589}-\x{5c7}\x{5f3}-'.
	'\x{61f}\x{640}\x{64b}-\x{65e}\x{66a}-\x{66d}\x{670}\x{6d4}\x{6d6}-\x{6ed}'.
	'\x{6fd}\x{6fe}\x{700}-\x{70f}\x{711}\x{730}-\x{74a}\x{7a6}-\x{7b0}\x{901}-'.
	'\x{903}\x{93c}\x{93e}-\x{94d}\x{951}-\x{954}\x{962}-\x{965}\x{970}\x{981}-'.
	'\x{983}\x{9bc}\x{9be}-\x{9cd}\x{9d7}\x{9e2}\x{9e3}\x{9f2}-\x{a03}\x{a3c}-'.
	'\x{a4d}\x{a70}\x{a71}\x{a81}-\x{a83}\x{abc}\x{abe}-\x{acd}\x{ae2}\x{ae3}'.
	'\x{af1}-\x{b03}\x{b3c}\x{b3e}-\x{b57}\x{b70}\x{b82}\x{bbe}-\x{bd7}\x{bf0}-'.
	'\x{c03}\x{c3e}-\x{c56}\x{c82}\x{c83}\x{cbc}\x{cbe}-\x{cd6}\x{d02}\x{d03}'.
	'\x{d3e}-\x{d57}\x{d82}\x{d83}\x{dca}-\x{df4}\x{e31}\x{e34}-\x{e3f}\x{e46}-'.
	'\x{e4f}\x{e5a}\x{e5b}\x{eb1}\x{eb4}-\x{ebc}\x{ec6}-\x{ecd}\x{f01}-\x{f1f}'.
	'\x{f2a}-\x{f3f}\x{f71}-\x{f87}\x{f90}-\x{fd1}\x{102c}-\x{1039}\x{104a}-'.
	'\x{104f}\x{1056}-\x{1059}\x{10fb}\x{10fc}\x{135f}-\x{137c}\x{1390}-\x{1399}'.
	'\x{166d}\x{166e}\x{1680}\x{169b}\x{169c}\x{16eb}-\x{16f0}\x{1712}-\x{1714}'.
	'\x{1732}-\x{1736}\x{1752}\x{1753}\x{1772}\x{1773}\x{17b4}-\x{17db}\x{17dd}'.
	'\x{17f0}-\x{180e}\x{1843}\x{18a9}\x{1920}-\x{1945}\x{19b0}-\x{19c0}\x{19c8}'.
	'\x{19c9}\x{19de}-\x{19ff}\x{1a17}-\x{1a1f}\x{1d2c}-\x{1d61}\x{1d78}\x{1d9b}-'.
	'\x{1dc3}\x{1fbd}\x{1fbf}-\x{1fc1}\x{1fcd}-\x{1fcf}\x{1fdd}-\x{1fdf}\x{1fed}-'.
	'\x{1fef}\x{1ffd}-\x{2070}\x{2074}-\x{207e}\x{2080}-\x{2101}\x{2103}-\x{2106}'.
	'\x{2108}\x{2109}\x{2114}\x{2116}-\x{2118}\x{211e}-\x{2123}\x{2125}\x{2127}'.
	'\x{2129}\x{212e}\x{2132}\x{213a}\x{213b}\x{2140}-\x{2144}\x{214a}-\x{2b13}'.
	'\x{2ce5}-\x{2cff}\x{2d6f}\x{2e00}-\x{3005}\x{3007}-\x{303b}\x{303d}-\x{303f}'.
	'\x{3099}-\x{309e}\x{30a0}\x{30fb}\x{30fd}\x{30fe}\x{3190}-\x{319f}\x{31c0}-'.
	'\x{31cf}\x{3200}-\x{33ff}\x{4dc0}-\x{4dff}\x{a015}\x{a490}-\x{a716}\x{a802}'.
	'\x{a806}\x{a80b}\x{a823}-\x{a82b}\x{d800}-\x{f8ff}\x{fb1e}\x{fb29}\x{fd3e}'.
	'\x{fd3f}\x{fdfc}-\x{fe6b}\x{feff}-\x{ff0f}\x{ff1a}-\x{ff20}\x{ff3b}-\x{ff40}'.
	'\x{ff5b}-\x{ff65}\x{ff70}\x{ff9e}\x{ff9f}\x{ffe0}-\x{fffd}');

	/**
	 * Matches all 'N' Unicode character classes (numbers)
	 */
	define('PREG_CLASS_NUMBERS',
	'\x{30}-\x{39}\x{b2}\x{b3}\x{b9}\x{bc}-\x{be}\x{660}-\x{669}\x{6f0}-\x{6f9}'.
	'\x{966}-\x{96f}\x{9e6}-\x{9ef}\x{9f4}-\x{9f9}\x{a66}-\x{a6f}\x{ae6}-\x{aef}'.
	'\x{b66}-\x{b6f}\x{be7}-\x{bf2}\x{c66}-\x{c6f}\x{ce6}-\x{cef}\x{d66}-\x{d6f}'.
	'\x{e50}-\x{e59}\x{ed0}-\x{ed9}\x{f20}-\x{f33}\x{1040}-\x{1049}\x{1369}-'.
	'\x{137c}\x{16ee}-\x{16f0}\x{17e0}-\x{17e9}\x{17f0}-\x{17f9}\x{1810}-\x{1819}'.
	'\x{1946}-\x{194f}\x{2070}\x{2074}-\x{2079}\x{2080}-\x{2089}\x{2153}-\x{2183}'.
	'\x{2460}-\x{249b}\x{24ea}-\x{24ff}\x{2776}-\x{2793}\x{3007}\x{3021}-\x{3029}'.
	'\x{3038}-\x{303a}\x{3192}-\x{3195}\x{3220}-\x{3229}\x{3251}-\x{325f}\x{3280}-'.
	'\x{3289}\x{32b1}-\x{32bf}\x{ff10}-\x{ff19}');

	/**
	 * Matches all 'P' Unicode character classes (punctuation)
	 */
	define('PREG_CLASS_PUNCTUATION',
	'\x{21}-\x{23}\x{25}-\x{2a}\x{2c}-\x{2f}\x{3a}\x{3b}\x{3f}\x{40}\x{5b}-\x{5d}'.
	'\x{5f}\x{7b}\x{7d}\x{a1}\x{ab}\x{b7}\x{bb}\x{bf}\x{37e}\x{387}\x{55a}-\x{55f}'.
	'\x{589}\x{58a}\x{5be}\x{5c0}\x{5c3}\x{5f3}\x{5f4}\x{60c}\x{60d}\x{61b}\x{61f}'.
	'\x{66a}-\x{66d}\x{6d4}\x{700}-\x{70d}\x{964}\x{965}\x{970}\x{df4}\x{e4f}'.
	'\x{e5a}\x{e5b}\x{f04}-\x{f12}\x{f3a}-\x{f3d}\x{f85}\x{104a}-\x{104f}\x{10fb}'.
	'\x{1361}-\x{1368}\x{166d}\x{166e}\x{169b}\x{169c}\x{16eb}-\x{16ed}\x{1735}'.
	'\x{1736}\x{17d4}-\x{17d6}\x{17d8}-\x{17da}\x{1800}-\x{180a}\x{1944}\x{1945}'.
	'\x{2010}-\x{2027}\x{2030}-\x{2043}\x{2045}-\x{2051}\x{2053}\x{2054}\x{2057}'.
	'\x{207d}\x{207e}\x{208d}\x{208e}\x{2329}\x{232a}\x{23b4}-\x{23b6}\x{2768}-'.
	'\x{2775}\x{27e6}-\x{27eb}\x{2983}-\x{2998}\x{29d8}-\x{29db}\x{29fc}\x{29fd}'.
	'\x{3001}-\x{3003}\x{3008}-\x{3011}\x{3014}-\x{301f}\x{3030}\x{303d}\x{30a0}'.
	'\x{30fb}\x{fd3e}\x{fd3f}\x{fe30}-\x{fe52}\x{fe54}-\x{fe61}\x{fe63}\x{fe68}'.
	'\x{fe6a}\x{fe6b}\x{ff01}-\x{ff03}\x{ff05}-\x{ff0a}\x{ff0c}-\x{ff0f}\x{ff1a}'.
	'\x{ff1b}\x{ff1f}\x{ff20}\x{ff3b}-\x{ff3d}\x{ff3f}\x{ff5b}\x{ff5d}\x{ff5f}-'.
	'\x{ff65}');

	/**
	 * Matches all CJK characters that are candidates for auto-splitting
	 * (Chinese, Japanese, Korean).
	 * Contains kana and BMP ideographs.
	 */
	define('PREG_CLASS_CJK', '\x{3041}-\x{30ff}\x{31f0}-\x{31ff}\x{3400}-\x{4db5}'.
	'\x{4e00}-\x{9fbb}\x{f900}-\x{fad9}');
}


function _likelize($str)
{
	return '%' . trim($str) . '%';
}

function query_google($search, $position, $limit)
{
	$site = 'atalian.com';
	$query = 'http://ajax.googleapis.com/ajax/services/search/web?' . http_build_query
	(
		array
		(
			'q' => $search . ' site:' . $site,
			'start' => $position * $limit,
			'v' => '1.0'
		)

		+ array
		(
			'gl' => 'fr',
			'hl' => 'fr',
			'rsz' => 'large'
		)
	);

	$rc = file_get_contents($query);

	$response = json_decode($rc)->responseData;

	$entries = array();
	$count = $response->cursor->estimatedResultCount;

	if ($response->results)
	{
		foreach ($response->results as $result)
		{
			$shortUrl = $result->unescapedUrl;
			$shortUrl = substr($shortUrl, strpos($shortUrl, $site) + strlen($site));

			$entries[] = (object) array
			(
				'url' => $shortUrl,
				'title' => $result->title,
				'body' => $result->content
			);
		}
	}

	return array($entries, $count);
}

function query_pages($search, $position, $limit)
{
	global $core;

	$model = $core->models['pages'];

	$query_part = 'FROM {self_and_related} INNER JOIN {self}__contents content ON (nid = pageid AND contentid = "body")
	WHERE is_online = 1 AND siteid = ? AND editor != "view" AND content LIKE ?';

	$query_args = array($core->site_id, '%' . $search . '%');

	$count = $model->query
	(
		'SELECT COUNT(nid) ' . $query_part, $query_args
	)
	->fetchColumnAndClose();

	$entries = $model->query
	(
		'SELECT node.*, page.*, content.content AS body ' . $query_part . ' ORDER BY created DESC LIMIT ' . ($position * $limit) . ', ' . $limit, $query_args
	)
	->fetchAll(PDO::FETCH_CLASS, 'ICanBoogie\ActiveRecord\Page', array($model));

	return array($entries, $count);
}

function query_contents($constructor, $search, $position, $limit)
{
	global $core;

	if ($constructor == 'contents')
	{
			$query_part = 'is_online = 1 AND (siteid = 0 OR siteid = ?)';
		$query_args = array($core->site_id);
	}
	else
	{
		$query_part = 'is_online = 1 AND (siteid = 0 OR siteid = ?) AND constructor = ?';
		$query_args = array($core->site_id, $constructor);
	}

	$model = $core->models[$constructor];

	$words = explode(' ', $search);
	$words = array_map('_likelize', $words);

	$properties = array('title', 'body');
	$concat = str_repeat(' AND CONCAT_WS(" ", ' . implode(', ', $properties) . ') LIKE ?', count($words));

	$query_part .= ' AND (' . substr($concat, 4) . ')';
	$query_args = array_merge($query_args, $words);

	$arq = $model->where($query_part, $query_args);

	$count = $arq->count;
	$entries = $arq->limit($position * $limit, $limit)->order('date DESC')->all;

	return array($entries, $count);
}





function make_set($constructor, $entries, $count, $search, $has_pager=false)
{
	global $core;

	$flat_id = 'module.' . strtr($constructor, '.', '_');

	$rc  = '<div class="set">';

	if (empty($_GET['constructor']))
	{
		$title = ($constructor == 'google' ? 'Google' : $core->modules->descriptors[$constructor][Module::T_TITLE]);
		$title = t(strtr($constructor, '.', '_'), array(), array('scope' => 'module_title', 'default' => $title));

		$rc .= '<h2>' . $title . '</h2>';
	}

	$rc .= '<p class="count">';
	$rc .= t('found', array(':count' => $count, '%search' => $search), array('scope' => array($flat_id, 'search')));
	$rc .= '</p>';

	if ($entries)
	{
		$rc .= '<ol>';

		foreach ($entries as $entry)
		{
			$rc .= '<li>';
			$rc .= '<h3><a href="' . $entry->url . '">' . wd_entities(\ICanBoogie\shorten($entry->title, 80)) . '</a></h3>';
			$rc .= '<cite>' . \ICanBoogie\shorten($entry->url, 64) . '</cite>';

			$excerpt = search_excerpt($search, html_entity_decode($entry->body, ENT_COMPAT, 'utf-8'));

			$rc .= '<p class="excerpt">' . $excerpt . '</p>';
			$rc .= '</li>';
		}

		$rc .= '</ol>';

		if ($count > count($entries))
		{
			if ($has_pager)
			{
				$rc .= new Pager
				(
					'div', array
					(
						Pager::T_COUNT => $count,
						Pager::T_LIMIT => $core->site->metas->get('search.limits.list', 10),
						Pager::T_POSITION => isset($_GET['page']) ? (int) $_GET['page'] : 0,
						Pager::T_WITH => 'q,constructor',

						'class' => 'pager'
					)
				);
			}
			else
			{
				$more_url = '?' . http_build_query(array('q' => $search, 'constructor' => $constructor));

				$rc .= '<p class="more"><a href="' . $more_url . '">';
				$rc .= t('more', array(':count' => $count, '%search' => $search), array('scope' => array($flat_id, 'search')));
				$rc .= '</a></p>';
			}
		}
	}

	$rc .= '</div>';

	return $rc;
}




/**
 * Returns snippets from a piece of text, with certain keywords highlighted.
 * Used for formatting search results.
 *
 * @param $keys
 *   A string containing a search query.
 *
 * @param $text
 *   The text to extract fragments from.
 *
 * @return
 *   A string containing HTML for the excerpt.
 */
function search_excerpt($keys, $text) {
  // We highlight around non-indexable or CJK characters.
  $boundary = '(?:(?<=['. PREG_CLASS_SEARCH_EXCLUDE . PREG_CLASS_CJK .'])|(?=['. PREG_CLASS_SEARCH_EXCLUDE . PREG_CLASS_CJK .']))';

  // Extract positive keywords and phrases
  preg_match_all('/ ("([^"]+)"|(?!OR)([^" ]+))/', ' '. $keys, $matches);
  $keys = array_merge($matches[2], $matches[3]);

  // Prepare text
  $text = ' '. strip_tags(str_replace(array('<', '>'), array(' <', '> '), $text)) .' ';
  array_walk($keys, '_search_excerpt_replace');
  $workkeys = $keys;

  // Extract a fragment per keyword for at most 4 keywords.
  // First we collect ranges of text around each keyword, starting/ending
  // at spaces.
  // If the sum of all fragments is too short, we look for second occurrences.
  $ranges = array();
  $included = array();
  $length = 0;
  while ($length < 256 && count($workkeys)) {
    foreach ($workkeys as $k => $key) {
      if (strlen($key) == 0) {
        unset($workkeys[$k]);
        unset($keys[$k]);
        continue;
      }
      if ($length >= 256) {
        break;
      }
      // Remember occurrence of key so we can skip over it if more occurrences
      // are desired.
      if (!isset($included[$key])) {
        $included[$key] = 0;
      }
      // Locate a keyword (position $p), then locate a space in front (position
      // $q) and behind it (position $s)
      if (preg_match('/'. $boundary . $key . $boundary .'/iu', $text, $match, PREG_OFFSET_CAPTURE, $included[$key])) {
        $p = $match[0][1];
        if (($q = strpos($text, ' ', max(0, $p - 60))) !== FALSE) {
          $end = substr($text, $p, 80);
          if (($s = strrpos($end, ' ')) !== FALSE) {
            $ranges[$q] = $p + $s;
            $length += $p + $s - $q;
            $included[$key] = $p + 1;
          }
          else {
            unset($workkeys[$k]);
          }
        }
        else {
          unset($workkeys[$k]);
        }
      }
      else {
        unset($workkeys[$k]);
      }
    }
  }

  // If we didn't find anything, return the beginning.
  if (count($ranges) == 0) {
    return truncate_utf8($text, 256) .' …';
  }

  // Sort the text ranges by starting position.
  ksort($ranges);

  // Now we collapse overlapping text ranges into one. The sorting makes it O(n).
  $newranges = array();
  foreach ($ranges as $from2 => $to2) {
    if (!isset($from1)) {
      $from1 = $from2;
      $to1 = $to2;
      continue;
    }
    if ($from2 <= $to1) {
      $to1 = max($to1, $to2);
    }
    else {
      $newranges[$from1] = $to1;
      $from1 = $from2;
      $to1 = $to2;
    }
  }
  $newranges[$from1] = $to1;

  // Fetch text
  $out = array();
  foreach ($newranges as $from => $to) {
    $out[] = substr($text, $from, $to - $from);
  }
  $text = (isset($newranges[0]) ? '' : '… ') . implode(' … ', $out) .' …';

  // Highlight keywords. Must be done at once to prevent conflicts ('strong' and '<strong>').
  $text = preg_replace('/'. $boundary .'('. implode('|', $keys) .')'. $boundary .'/iu', '<strong>\0</strong>', $text);
  return $text;
}

/**
 * @} End of "defgroup search".
 */

/**
 * Helper function for array_walk in search_except.
 */
function _search_excerpt_replace(&$text) {
  $text = preg_quote($text, '/');
}

function search_forms() {
  $forms['search_theme_form']= array(
    'callback' => 'search_box',
    'callback arguments' => array('search_theme_form'),
  );
  $forms['search_block_form']= array(
    'callback' => 'search_box',
    'callback arguments' => array('search_block_form'),
  );
  return $forms;
}



/**
 * Truncate a UTF-8-encoded string safely to a number of characters.
 *
 * @param $string
 *   The string to truncate.
 * @param $len
 *   An upper limit on the returned string length.
 * @param $wordsafe
 *   Flag to truncate at last space within the upper limit. Defaults to FALSE.
 * @param $dots
 *   Flag to add trailing dots. Defaults to FALSE.
 * @return
 *   The truncated string.
 */
function truncate_utf8($string, $len, $wordsafe = FALSE, $dots = FALSE) {

  if (mb_strlen($string) <= $len) {
    return $string;
  }

  if ($dots) {
    $len -= 4;
  }

  if ($wordsafe) {
    $string = mb_substr($string, 0, $len + 1); // leave one more character
    if ($last_space = strrpos($string, ' ')) { // space exists AND is not on position 0
      $string = substr($string, 0, $last_space);
    }
    else {
      $string = mb_substr($string, 0, $len);
    }
  }
  else {
    $string = mb_substr($string, 0, $len);
  }

  if ($dots) {
    $string .= ' ...';
  }

  return $string;
}