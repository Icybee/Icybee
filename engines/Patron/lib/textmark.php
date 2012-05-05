<?php

/*
 * This file is part of the Patron package.
 *
 * (c) Olivier Laviale <olivier.laviale@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use ICanBoogie\Hook;

@define('MARKDOWN_PARSER_CLASS', 'Textmark_Parser');

/*
 @define('txt_apostrophe',		  '&rsquo;');
 //@define('txt_quote_single_open',  '&#8216;');
 //@define('txt_quote_single_close', '&#8217;');
 @define('txt_quote_double_open',  '&laquo;&nbsp;');
 @define('txt_quote_double_close', '&nbsp;&raquo;');
 @define('txt_prime',			  '&prime;');
 @define('txt_prime_double', 	  '&Prime;');
 @define('txt_ellipsis', 		  '&hellip;');
 @define('txt_emdash',			  '&mdash;');
 @define('txt_endash',			  '&ndash;');
 @define('txt_dimension',		  '&times;');
 @define('txt_trademark',		  '&trade;');
 @define('txt_registered',		  '&reg;');
 @define('txt_copyright',		  '&copy;');
 */
@define('txt_apostrophe',		  '\'');
@define('txt_quote_double_open',  '« ');
@define('txt_quote_double_close', ' »');
@define('txt_prime',			  'ʹ');
@define('txt_prime_double', 	  'ʺ');
@define('txt_ellipsis', 		  '…');
@define('txt_emdash',			  '—');
@define('txt_endash',			  '–');
@define('txt_dimension',		  '×');
@define('txt_trademark',		  '™');
@define('txt_registered',		  '®');
@define('txt_copyright',		  '©');

require_once 'markdown/markdown.php';
require_once 'markdown/markdown_extras.php';

class Textmark_Parser extends MarkdownExtra_Parser
{
	const NBSP = "\xC2\xA0";
	const NBSP_TAB = "\xC2\xA0\xC2\xA0\xC2\xA0\xC2\xA0";

	static public function parse($str)
	{
		return Markdown((string) $str);
	}

	function Textmark_Parser()
	{
		$this->early_gamut += array
		(
			'doShell' => 5
		);

		$this->span_gamut += array
		(
			'doFlash' => 9, // just before doImages
			'doGlyphs' => 70,
			'doSpan' => 71
		);

		parent::MarkdownExtra_Parser();
	}

	public function doShell($text)
	{
		return preg_replace_callback
		(
			'{^\$\s+([^\n]+)\n}xm', array($this, 'doShell_callback'), $text
		);

		return $text;
	}

	protected function doShell_callback($matches)
	{
		$text = '<pre class="markdown shell">$ ' . $matches[1] . '</pre>' . PHP_EOL;

		return $this->hashBlock($text);
	}

	/*
	 **

	 SYSTEM

	 **
	 */

	function hashHTMLBlocks($text)
	{
		/*
		$text = preg_replace_callback
		(
			'#^\@([a-z]+)(.*?)\1\@$#sm', array(&$this, '_doSourceCode'), $text
		);
		*/

		return parent::hashHTMLBlocks($text);
	}

	function _doSourceCode($matches)
	{
		//		\ICanBoogie\log('\1 :: matches: \2', __FUNCTION__, $matches);

		return $this->hashBlock($this->doSourceCode($matches[1], $matches[2]));
	}

	function doSourceCode($type, $text)
	{
		$text = trim($text);

		switch ($type)
		{
			case 'php': return '<pre class="php"><code>' . $this->doSourcePHP($text) . '</code></pre>';
			case 'html': return '<pre class="html"><code>' . $this->doSourceHTML($text) . '</code></pre>';
			case 'raw': return '<pre><code>' . $this->doSourceRaw($text) . '</code></pre>';
			case 'publish': return $this->doSourcePublish($text);
		}

		\ICanBoogie\log_error('\1: unknown source type "\1"', __FUNCTION__, $type);

		return $text;
	}

	private function doSourcePublish($text)
	{
		return Patron($text);
	}

	/*
	 **

	 HEADERS

	 **
	 */

	function doHeaders($text)
	{
		/*

		Setext-style headers:

		Header 1
		########

		Header 2
		========

		Header 3
		--------

		$text = preg_replace_callback
		(
		'{ ^(.+?)[ ]*\n(\#+|=+|-+)[ ]*\n+ }mx',

		array(&$this, '_doHeaders_callback_setext'), $text
		);

		*/

		$text = parent::doHeaders($text);

		/*

		atx-style headers:

		h1. Header 1
		h2. Header 2
		...
		h6. Header 6

		*/

		$text = preg_replace_callback
		(
			'{
				^h([1-6])\.	# $1 = string of h?
				[ ]*
				(.+?)		# $2 = Header text
				[ ]*
				\n+
			}xm',

		array(&$this, '_doHeaders_callback_tp'), $text
		);

		return $text;
	}

	function _doHeaders_callback_setext($matches)
	{
		switch ($matches[3]{0})
		{
			case '#': $level = 1; break;
			case '=': $level = 2; break;
			case '-': $level = 3; break;
		}

		$block = "<h$level>" . $this->runSpanGamut($matches[1]) . "</h$level>";

		return "\n" . $this->hashBlock($block) . "\n\n";
	}

	function _doHeaders_callback_tp($matches)
	{
		//		\ICanBoogie\log('<pre>doHeaders[atx]: \1</pre>', print_r($matches, true));

		$level = $matches[1];
		$block = "<h$level>" . $this->runSpanGamut($matches[2]) . "</h$level>";
		return "\n" . $this->hashBlock($block) . "\n\n";
	}

	/*
	 **

	 SPAN

	 **
	 */

	function doSpan($text)
	{
		$clas = "(?:\([^)]+\))";
		$styl = "(?:\{[^}]+\})";
		$lnge = "(?:\[[^]]+\])";
		$hlgn = "(?:\<(?!>)|(?<!<)\>|\<\>|\=|[()]+(?! ))";

		$c = "(?:{$clas}|{$styl}|{$lnge}|{$hlgn})*";

		$qtags = array('\*\*','\*','\?\?','-','__','_','%','\+','~','\^');
		$pnct = ".,\"'?!;:";

		foreach($qtags as $f) {
			$text = preg_replace_callback("/
				(^|(?<=[\s>$pnct\(])|[{[])
				($f)(?!$f)
				(" . $c . ")
				(?::(\S+))?
				([^\s$f]+|\S.*?[^\s$f\n])
				([$pnct]*)
				$f
				($|[\]}]|(?=[[:punct:]]{1,2}|\s|\)))
			/x", array(&$this, "fSpan"), $text);
		}
		return $text;
	}

	function fSpan($m)
	{
		$qtags = array(
			'*'  => 'strong',
			'**' => 'b',
			'??' => 'cite',
			'_'  => 'em',
			'__' => 'i',
			'-'  => 'del',
			'%'  => 'span',
			'+'  => 'ins',
			'~'  => 'sub',
			'^'  => 'sup'
			);

			list(, $pre, $tag, $atts, $cite, $content, $end, $tail) = $m;
			$tag = $qtags[$tag];
			//		$atts = $this->pba($atts);
			//		$atts .= ($cite != '') ? 'cite="' . $cite . '"' : '';

			$out = "<$tag$atts>$content$end</$tag>";

			if (($pre and !$tail) or ($tail and !$pre))
			{
				$out = $pre.$out.$tail;
			}

			//		$this->dump($out);

			return $out;

	}

	function doGlyphs($text)
	{
		//		echo l('doGlyphs: "\1"<br />', wd_entities($text));

		$glyph_search = array
		(
		//		'/(\w)\'(\w)/', 									 // apostrophe's
			'/(\s)\'(\d+\w?)\b(?!\')/u', 						 // back in '88
		//		'/(\S)\'(?=\s|[[:punct:]]|<|$)/',						 //  single closing
		//		'/\'/', 											 //  single opening
			'/(\S)\"(?=\s|[[:punct:]]|<|$)/u',						 //  double closing
			'/"/',												 //  double opening
			'/\b([A-Z][A-Z0-9]{2,})\b(?:[(]([^)]*)[)])/u',		 //  3+ uppercase acronym
			'/(?<=\s|^|[>(;-])([A-Z]{3,})([a-z]*)(?=\s|[[:punct:]]|<|$)/u',  //  3+ uppercase
			'/([^.]?)\.{3}/u',									 //  ellipsis
			'/(\s?)--(\s?)/u',									 //  em dash
			'/\s-(?:\s|$)/u',									 //  en dash
			'/(\d+)( ?)x( ?)(?=\d+)/u',							 //  dimension sign
			'/(\b ?|\s|^)[([]TM[])]/iu', 						 //  trademark
			'/(\b ?|\s|^)[([]R[])]/iu',							 //  registered
			'/(\b ?|\s|^)[([]C[])]/iu',							 //  copyright

		#
		# the following is for french language
		#

			'#\s(\!|\?|\:|\;|\-)#u',
		);

		$glyph_replace = array
		(
		//		'$1'.$txt_apostrophe.'$2',			 // apostrophe's
			'$1'.txt_apostrophe.'$2',			 // back in '88
		//		'$1'.$txt_quote_single_close,		 //  single closing
		//		$txt_quote_single_open, 			 //  single opening
			'$1'.txt_quote_double_close,		 //  double closing
		txt_quote_double_open, 			 //  double opening
			'<acronym title="$2">$1</acronym>',  //  3+ uppercase acronym
			'<span class="caps">$1</span>$2',	 //  3+ uppercase
			'$1'.txt_ellipsis, 				 //  ellipsis
			'$1'.txt_emdash.'$2',				 //  em dash
			' '.txt_endash.' ',				 //  en dash
			'$1$2'.txt_dimension.'$3', 		 //  dimension sign
			'$1'.txt_trademark,				 //  trademark
			'$1'.txt_registered,				 //  registered
			'$1'.txt_copyright,				 //  copyright

		#
		# the following is for french language
		#

		self::NBSP . '$1',
		);

		return preg_replace($glyph_search, $glyph_replace, $text);
	}

	/*
	 **

	 BLOCS

	 **
	 */

	function formParagraphs($text)
	{
		#
		#	Params:
		#		$text - string to process with html <p> tags
		#
		# Strip leading and trailing lines:

		$text = preg_replace('/\A\n+|\n+\z/', '', $text);
		$grafs = preg_split('/\n{2,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

		#
		# Wrap <p> tags and unhashify HTML blocks
		#
		foreach ($grafs as $key => $value)
		{
			//			\ICanBoogie\log('in \1, graf: [<tt>\2</tt>]', __FUNCTION__, $value);

			#
			# styles
			#

			$style = NULL;

			if (preg_match
			(
				'{
					(						# the whole thing is saved in $1
					^p						# start
					(\<|\||\>|\=)?			# alignement $2
					\.						# separator
					[ ]+					# white space, at least one is mandatory
					)
				}sx', $value, $matches
			))
			{
				$value = substr($value, strlen($matches[1]));
				$align = $matches[2];

				if ($align)
				{
					switch ($align)
					{
						case '<': $align = 'left'; break;
						case '|': $align = 'center'; break;
						case '>': $align = 'right'; break;
						case '=': $align = 'justify'; break;
					}

					$style .= "text-align: $align;";
				}
			}

			//			\ICanBoogie\log('in \1, graf: <tt>\2</tt>, match: <pre>\3</pre>', __FUNCTION__, $value, $matches);


			#
			#
			#

			$value = trim($this->runSpanGamut($value));

			# Check if this should be enclosed in a paragraph.
			# Clean tag hashes & block tag hashes are left alone.

			$is_p = !preg_match('/^B\x1A[0-9]+B|^C\x1A[0-9]+C$/', $value);

			if ($is_p)
			{
				if ($style)
				{
					$value = '<p style="' . $style . '">' . $value . '</p>';
				}
				else
				{
					$value = "<p>$value</p>";
				}
			}

			$grafs[$key] = $value;
		}

		# Join grafs in one text, then unhash HTML tags.
		$text = implode("\n\n", $grafs);

		# Finish by removing any tag hashes still present in $text.
		$text = $this->unhash($text);

		return $text;
	}

	/*
	 **

	 STYLES

	 **
	 */

	function _doItalicAndBold_em_callback($matches)
	{
		$text = $matches[2];
		$text = $this->runSpanGamut($text);

		$tag = ($matches[1] == '*') ? 'strong' : 'em';

		return $this->hashPart("<$tag>$text</$tag>");
	}

	function _doItalicAndBold_strong_callback($matches)
	{
		$text = $matches[2];
		$text = $this->runSpanGamut($text);

		$tag = ($matches[1] == '**') ? 'b' : 'i';

		return $this->hashPart("<$tag>$text</$tag>");
	}

	/*
	 **

	 FLASH

	 **
	 */

	function doFlash($text)
	{
		#
		# Turn Textmark flash shortcuts into <object> tags.
		#

		#
		# Next, handle inline images:  ![alt text](url "optional title")
		# Don't forget: encode * and _
		#

		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  @
			  	(\<|\||\>|\=)?							# alignment = $2
			  \[
				('.$this->nested_brackets_re.')			# title = $3
			  \]
			  \s?			# One optional whitespace character
			  \(			# literal paren
				[ ]*
				(?:
					<(\S*)>								# src url = $4
				|
					('.$this->nested_url_parenthesis_re.')	# src url = $5
				)
				[ ]*
				(			# $6
				  ([\'"])	# quote char = $7
				  (.*?)		# title = $8
				  \6		# matching quote
				  [ ]*
				)?			# title is optional
			  \)
			)
			}xs',
		array(&$this, '_doFlash_inline_callback'), $text);

		return $text;
	}

	function _doFlash_inline_callback($matches)
	{
		//		echo l('matches: \1', $matches);

		$whole_match = $matches[1];
		$align = $matches[2];
		$alt_text = $matches[3];
		$url = empty($matches[4]) ? $matches[5] : $matches[4];
		$title =& $matches[8];

		$parts = parse_url($url);

		//		echo l('parsed url: \1', $parts);

		$data = NULL;
		$width = 0;
		$height = 0;

		preg_match('#www\.([^\.]+)#', $parts['host'], $matches);

		//		echo l('host: \1', $matches);

		switch ($matches['1'])
		{
			case 'youtube':
				{
					preg_match('#v=([^\&]+)#', $parts['query'], $matches);

					$data = 'http://www.youtube.com/v/' . $matches[1] . '&amp;fs=1&amp;rel=1&amp;border=0';
					$width = 420;
					$height = 360;
				}
				break;

			case 'dailymotion':
				{
					preg_match('#video\/([^_]+)#', $parts['path'], $matches);

					//				echo l('query: \1', $matches);

					$data = 'http://www.dailymotion.com/swf/' . $matches[1];
					$width = 420;
					$height = 360;
				}
				break;
		}

		$rc  = '<object width="' . $width . '" height="' . $height . '"';
		$rc .= ' type="application/x-shockwave-flash"';

		if ($align)
		{
			switch ($align)
			{
				case '<': $align = 'left'; break;
				case '=':
				case '|': $align = 'middle'; break;
				case '>': $align = 'right'; break;
			}

			$rc .= ' align="' . $align . '"';
		}

		$rc .= ' data="' . $data . '">';
		$rc .= '<param name="wmode" value="transparent" />';
		$rc .= '<param name="movie" value="' . $data . '" />';
		$rc .= '<param name="allowfullscreen" value="true" />';
		$rc .= '<param name="allowscriptaccess" value="always" />';
		$rc .= '</object>';

		if ($alt_text)
		{
			$rc .= '<br />';
			$rc .= '<a href="' . $url . '">' . wd_entities($alt_text) . '</a>';
		}

		return $this->hashPart($rc);
	}

	/*
	 **

	 IMAGES

	 **
	 */
	function doImages($text)
	{
		#
		# Turn Markdown image shortcuts into <img> tags.
		#

		#
		# gofromiel: added align options
		#

		#
		# First, handle reference-style labeled images: ![alt text][id]
		#

		$text = preg_replace_callback
		(
			'{
			(										# wrap whole match in $1
			  !										# start
			  (\<|\||\>|\=)?							# alignment = $2
			  \[
				('.$this->nested_brackets_re.')		# alt text = $3
			  \]

			  [ ]?				# one optional space
			  (?:\n[ ]*)?		# one optional newline followed by spaces

			  \[
				(.*?)								# id = $4
			  \]

			)
			}xs', array(&$this, '_doImages_reference_callback'), $text
		);

		#
		# Next, handle inline images:  ![alt text](url "optional title")
		# Don't forget: encode * and _
		#
		$text = preg_replace_callback('{
			(				# wrap whole match in $1
			  !
			  	(\<|\||\>|\=)?							# alignment = $2
			  \[
				('.$this->nested_brackets_re.')		# alt text = $3
			  \]
			  \s?			# One optional whitespace character
			  \(			# literal paren
				[ ]*
				(?:
					<(\S*)>	# src url = $4
				|
					('.$this->nested_url_parenthesis_re.')	# src url = $5
				)
				[ ]*
				(			# $6
				  ([\'"])	# quote char = $7
				  (.*?)		# title = $8
				  \6		# matching quote
				  [ ]*
				)?			# title is optional
			  \)
			)
			}xs',
		array(&$this, '_doImages_inline_callback'), $text);

		return $text;
	}

	static protected $images_reference_callback;

	function _doImages_reference_callback($matches)
	{
		if (self::$images_reference_callback === null)
		{
			$hook = false;

			try
			{
				$hook = Hook::find('textmark', 'images.reference');

				if (!$hook)
				{
					$hook = false;
				}
			}
			catch (Exception $e) {}

			self::$images_reference_callback = $hook;
		}

		if (self::$images_reference_callback !== false)
		{
			return $this->hashPart(self::$images_reference_callback->__invoke(array(), $this, $matches));
		}
























		static $module;

		if (!$module)
		{
			global $core;

			$module = $core->modules['images'];
		}

		//		echo l('<pre>in \1: \2</pre>', __FUNCTION__, $matches);

		$align = $matches[2];
		$alt = $matches[3];
		$id = $matches[4];

		# for shortcut links like ![this][].

		if (!$id)
		{
			$id = $alt;
		}

		$parts = explode(':', $id, 2);

		if (isset($parts[1]))
		{
			$entry = $module->model()->loadRange
			(
			0, 1, 'WHERE `' . $module->getConstant('TITLE') . '` = ? AND `' . $module->getConstant('ALBUM') . '` = ?', array
			(
			$parts[1], $parts[0]
			)
			)
			->fetchAndClose();

		}
		else
		{
			$entry = $module->model()->loadRange
			(
			0, 1, 'WHERE `slug` = ? OR `title` = ?', array
			(
			$id, $id
			)
			)
			->fetchAndClose();
		}

		if (!$entry)
		{
			$matches[2] = $matches[3];
			$matches[3] = $matches[4];

			return parent::_doImages_reference_callback($matches);
		}

		$params = array
		(
			'src' => $entry->path,
			'alt' => wd_entities($alt),
			'width' => $entry->width,
			'height' => $entry->height
		);

		if ($align)
		{
			switch ($align)
			{
				case '<': $align = 'left'; break;
				case '=':
				case '|': $align = 'middle'; break;
				case '>': $align = 'right'; break;
			}

			$params['align'] = $align;
		}

		# the image has been found is the database

		return $this->hashPart($this->createElement('img', $params));
	}

	function _doImages_inline_callback($matches)
	{
		//		\ICanBoogie\log('<pre>in \1: \2</pre>', __FUNCTION__, $matches);

		$whole_match	= $matches[1];
		$align = $matches[2];
		$alt_text		= $matches[3];
		$url			= $matches[4] == '' ? $matches[5] : $matches[4];
		$title			=& $matches[8];

		$alt_text = str_replace('"', '&quot;', $alt_text);
		$result = "<img src=\"$url\" alt=\"$alt_text\"";

		if ($align)
		{
			switch ($align)
			{
				case '<': $align = 'left'; break;
				case '=':
				case '|': $align = 'middle'; break;
				case '>': $align = 'right'; break;
			}

			$result .= ' align="' . $align . '"';
		}

		if (isset($title))
		{
			$title = str_replace('"', '&quot;', $title);
			$result .=  " title=\"$title\""; # $title already quoted
		}

		$result .= $this->empty_element_suffix;

		return $this->hashPart($result);
	}

	/*
	 **

	 NEW IMPLEMENTATIONS

	 **
	 */

	function createElement($markup, $attrs, $body=NULL)
	{
		$rc = array();

		foreach ($attrs as $name => $value)
		{
			$rc[] = $name . '="' . $value . '"';
		}

		return "<$markup " . implode(' ', $rc) . ($body ? ">$body</$markup>" : " />");
	}

	protected function format_codeblock($codeblock, $type)
	{
		switch ($type)
		{
			case 'html': return $this->doSourceHTML($codeblock);
			case 'php': return $this->doSourcePHP($codeblock);
			default: return $this->doSourceRaw($codeblock);
		}
	}

	#
	# raw source
	#

	function doSourceRaw($text)
	{
		$text = wd_entities($text);
		$text = str_replace("\t", self::NBSP_TAB, $text);
		$text = str_replace(" ", self::NBSP, $text);

		return $text;
	}

	#
	# HTML source highlighter
	#

	protected function doSourceHTML($text)
	{
		//		\ICanBoogie\log('## \1 ## <pre>\2</pre>', __FUNCTION__, $text);

		$text = trim($text);

		#
		# markup
		#

		$text = preg_replace_callback
		(
			'#(\<\!?[^\s^\>]+)(\s+[^\>]+)?(\/?\>)#m', array($this, '_do_html_markup'), $text
		);

		//		\ICanBoogie\log('## \1 ## <pre>\2</pre>', __FUNCTION__, $text);

		#
		# markup close
		#

		//		\ICanBoogie\log('## \1 ## <pre>\2</pre>', __FUNCTION__, $text);

		$text = preg_replace_callback
		(
			'#\<\/[a-zA-Z]+\>#m', array($this, '_do_html_markup_close'), $text
		);

		#
		# tabulations and spaces
		#

		//		\ICanBoogie\log('## \1 ## <pre>\2</pre>', __FUNCTION__, wd_entities($text));

		$text = wd_entities($text);
		$text = str_replace("\t", self::NBSP_TAB, $text);
		$text = str_replace(" ", self::NBSP, $text);

		return $text;
	}

	function _do_html_string($matches)
	{
		//		\ICanBoogie\log('<pre>in \1: \2</pre>', __FUNCTION__, $matches);

		return $this->hashPart('<span class="string">' . wd_entities($matches[0]) . '</span>');
	}

	function _do_html_attribute($matches)
	{
		//		\ICanBoogie\log('<pre>in \1: \2</pre>', __FUNCTION__, $matches);

		return $this->hashPart('<span class="attribute">' . wd_entities($matches[0]) . '</span>');
	}

	function _do_html_markup($matches)
	{
		//		\ICanBoogie\log('<pre>in \1: \2</pre>', __FUNCTION__, $matches);

		$text = $matches[2];

		#
		# strings
		#

		$text = preg_replace_callback
		(
			'#\"[^\"]+\"#', array(&$this, '_do_html_string'), $text
		);

		//		\ICanBoogie\log('<pre>in \1: \2</pre>', __FUNCTION__, $text);

		#
		# attributes
		#

		$text = preg_replace_callback
		(
			'#[^\s\=]+#', array(&$this, '_do_html_attribute'), $text
		);

		$rc = $this->hashPart('<span class="markup">' . wd_entities($matches[1]) . '</span>');
		$rc .= $text;
		$rc .= $this->hashpart('<span class="markup">' . wd_entities($matches[3]) . '</span>');

		return $rc;
	}

	function _do_html_markup_close($matches)
	{
		//		\ICanBoogie\log('<pre>in \1: \2</pre>', __FUNCTION__, $matches);

		return $this->hashPart('<span class="markup">' . wd_entities($matches[0]) . '</span>');
	}

	#
	# PHP source highlighter
	#

	function doSourceCommentLine($text, $marker)
	{
		$lines = explode("\n", $text);

		//		echo l('lines: \1', $lines);

		$marker_len = strlen($marker);

		foreach ($lines as &$line)
		{
			$in_quotes = NULL;

			for ($i = 0 ; $i < strlen($line) ; $i++)
			{
				$c = $line{$i};

				if (($c == $in_quotes) && (($i > 1) && ($line{$i - 1} != '\\')))
				{
					$in_quotes = NULL;
				}
				else if ($in_quotes)
				{
					continue;
				}
				else if (($c == '\'') || ($c == '"'))
				{
					$in_quotes = $c;
				}
				else if (substr($line, $i, $marker_len) == $marker)
				{
					//					echo l('found marker at \1 (\2)<br />', $i, wd_entities(substr($line, $i, 16)));

					$line =

					substr($line, 0, $i) .
					$this->hashPart('<code class="comment">' . wd_entities(substr($line, $i)) . '</code>');

					break;
				}
			}
		}

		return implode("\n", $lines);
	}

	const QUOTE_SINGLE = '\'';
	const QUOTE_DOUBLE = '"';
	const ESCAPE = '\\';

	function doSourceString($text)
	{
		$out = NULL;
		$swap = NULL;

		$quote = NULL;
		$quote_start = null;
		$escaped = false;

		$y = strlen($text);

		for ($i = 0 ; $i < $y ; $i++)
		{
			$c = $text{$i};

			if (($c == self::QUOTE_SINGLE || $c == self::QUOTE_DOUBLE) && ($quote_start === null || $quote == $c))
			{
				if ($escaped)
				{
					$escaped = false;

					continue;
				}
				else if ($quote_start !== null && $c == $quote)
				{
					$out .= $this->hashPart('<span class="string">' . wd_entities($quote . substr($text, $quote_start + 1, $i - $quote_start - 1) . $quote) . '</span>');

					$quote_start = null;
					$quote = null;
				}
				else
				{
					$quote = $c;
					$quote_start = $i;
				}
			}
			else
			{
				if ($c == '\\')
				{
					$escaped = !$escaped;
				}
				else
				{
					$escaped = false;
				}

				if ($quote_start === null)
				{
					$out .= $c;
				}
			}
		}

		return $out;
	}

	function doSourcePHP($text)
	{
		//		\ICanBoogie\log('## \1 ## <pre>\2</pre>', __FUNCTION__, $text);

		$text = preg_replace('#^\<\?php\s*#', '', trim($text)); // FXME-20110817: this is a compat because we don't require the <?php and we automatically add it, whereas Git requires it

		$text = $this->doSourceCommentLine($text, '#');
		$text = $this->doSourceCommentLine($text, '//');

		#
		# comment block
		#

		$text = preg_replace_callback
		(
			'#/\*.*?\*/#ms', array(&$this, '_do_php_comment'), $text
		);

		$text = $this->doSourceString($text);

		#
		# functions
		#

		$text = preg_replace_callback
		(
			'#(\$?[a-zA-z0-9_]+)\(#', array(&$this, '_do_php_function'), $text
		);

		#
		# variables
		#

		//		\ICanBoogie\log('## \1 ## <pre>\2</pre>', __FUNCTION__, wd_entities($text));

		$text = preg_replace_callback
		(
			'#(\$|\-\>)([a-zA-z0-9_]+)(?!\x1A)#', array(&$this, '_do_php_variable'), $text
		);

		#
		# numbers
		#

		$text = preg_replace_callback
		(
			'#0x[0-9a-fA-F]{1,8}#u', array(&$this, '_do_php_number'), $text
		);

		$text = preg_replace_callback
		(
			'#(?<!\x1A)\d+(?![\w])#u', array(&$this, '_do_php_number'), $text
		);

		#
		# reserved keywords
		#

		$reserved = array
		(
			'include_once',
			'require_once',
			'endswitch',
			'namespace',
			'protected',
			'continue',
			'endwhile',
			'function',
			'default',
			'include',
			'require',
			'extends',
			'foreach',
			'private',
			'elseif',
			'global',
			'parent',
			'static',
			'return',
			'switch',
			'public',
			'break',
			'class',
			'const',
            'endif',
			'case',
			'true',
			'self',
			'echo',
			'TRUE',
			'else',
			'false',
			'FALSE',
			'while',
			'NULL',
			'for',
			'new',
			'use',
			'var',
			'as',
			'if',
			'do',
		);

		foreach ($reserved as $k)
		{
			$text = preg_replace_callback
			(
				'#' . $k . '#', array(&$this, '_do_php_reserved'), $text
			);
		}

		#
		# symbols
		#

		$text = preg_replace_callback
		(
			'#[\(\)\[\]\{\}\!\@\%\&\*\|\/\<\>\-\+\=]+#', array(&$this, '_do_php_symbol'), $text
		);

		#
		# tabulations and spaces
		#

		//		\ICanBoogie\log('## \1 ## <pre>\2</pre>', __FUNCTION__, wd_entities($text));

		$text = str_replace("\t", self::NBSP_TAB, $text);
		$text = str_replace(" ", self::NBSP, $text);

		//		preg_match('#\/\*(.*)\*\/#', $text, $matches);

		$text =

			'<span class="delimiter">' .
			"&lt;?php</span>\n\n" .

		$text;

		//			'<span class="delimiter">' .
		//			"\n\n?&gt;</span>" .


//		$text = nl2br($text);

		return $text;
	}










	function _do_codewd_entities($text)
	{
		$text = wd_entities($text);
		$text = str_replace("\t", self::NBSP_TAB, $text);
		$text = str_replace(" ", self::NBSP, $text);

		return $text;
	}
	/*
	 function _do_php_string($matches)
	 {
	 //		\ICanBoogie\log('## \1 ## \2', __FUNCTION__, $matches);

		return $this->hashPart('<span class="string">' . $this->_do_codewd_entities($matches[0]) . '</span>');
		}
		*/

	function _do_php_comment($matches)
	{

		//		\ICanBoogie\log('## \1 ## \2', __FUNCTION__, $matches);

		return $this->hashPart
		(
			'<span class="comment">' .
		$this->_do_codewd_entities($matches[0]) .
			'</span>'
			);
	}

	function _do_php_variable($matches)
	{
		//		\ICanBoogie\log('## \1 ## \2', __FUNCTION__, $matches);

		if ($matches[1] == '->')
		{
			return '->' . $this->hashPart('<span class="variable">' . $matches[2] . '</span>');
		}

		return $this->hashPart('<span class="variable">' . $matches[0] . '</span>');
	}

	function _do_php_reserved($matches)
	{
		//		\ICanBoogie\log('## \1 ## \2', __FUNCTION__, $matches);

		return $this->hashPart('<span class="reserved">' . $matches[0] . '</span>');
	}

	function _do_php_function_def($matches)
	{
		//		\ICanBoogie\log('## \1 ## \2', __FUNCTION__, $matches);

		return $this->hashPart('<span class="function">function</span>' . $matches[1]);
		//		return $this->hashPart('<span class="function">' . $matches[1] . '</span>(', 'F');
	}

	function _do_php_function($matches)
	{
		//		\ICanBoogie\log('## \1 ## \2', __FUNCTION__, $matches);

		if ($matches[1]{0} == '$')
		{
			return $matches[0];
		}

		return $this->hashPart('<span class="function">' . $matches[1] . '</span>', 'F'). '(';
	}

	function _do_php_symbol($matches)
	{
		return $this->hashPart('<span class="symbol">' . wd_entities($matches[0]) . '</span>');
	}

	function _do_php_number($matches)
	{
		//		\ICanBoogie\log('## \1 ## \2', __FUNCTION__, $matches);

		return $this->hashPart('<span class="number">' . $matches[0] . '</span>');
	}

	/*
	 function _do_php_callback($matches)
	 {
	 //		\ICanBoogie\log('<pre>in \1: \2</pre>', __FUNCTION__, $matches);

		return 'class="' . $this->php_colors[$matches[1]] . '"';
		}
		*/

	function _doAnchors_inline_callback($matches)
	{
		$whole_match	=  $matches[1];
		$link_text		=  $this->runSpanGamut($matches[2]);
		$url			=  $matches[3] == '' ? $matches[4] : $matches[3];
		$title			=& $matches[7];

		if ($matches[2] == 'video:')
		{
			$w = 500;
			$h = 375;
			$rc = '<p>Video link type not recognized: ' . wd_entities($url) . '</p>';

			if (preg_match('#vimeo.com/(\d+)#', $url, $matches))
			{
				$id = $matches[1];
				$data = wd_entities("http://vimeo.com/moogaloop.swf?clip_id=$id&server=vimeo.com&show_title=1&show_byline=1&show_portrait=0&color=F65FB8&fullscreen=1");

				$rc = <<<EOT
<p align="center">
	<object width="$w" height="$h" data="$data" type="application/x-shockwave-flash">
		<param name="movie" value="$data" />
		<param name="wmode" value="transparent" />
		<param name="allowfullscreen" value="true" />
		<param name="allowscriptaccess" value="always" />
	</object>
</p>
EOT;

			}
			else if (preg_match('#youtube.com/(watch)?\?v=([^\&\#]+)#', $url, $matches))
			{
				$id = $matches[2];
				$data = wd_entities("http://www.youtube.com/v/$id&fs=1&rel=1&border=0&color1=0x993C72&color2=0xF65FB8");

				$rc = <<<EOT
<p align="center">
	<object width="$w" height="$h" data="$data" type="application/x-shockwave-flash">
		<param name="movie" value="$data" />
		<param name="wmode" value="transparent" />
		<param name="allowfullscreen" value="true" />
		<param name="allowscriptaccess" value="always" />
	</object>
</p>
EOT;
			}
			else if (preg_match('#dailymotion.com/video/([^_]+)#', $url, $matches))
			{
				$id = $matches[1];
				$data = wd_entities("http://www.dailymotion.com/swf/$id");

				$rc = <<<EOT
<p align="center">
	<object width="$w" height="$h" data="$data" type="application/x-shockwave-flash">
		<param name="movie" value="$data" />
		<param name="wmode" value="transparent" />
		<param name="allowfullscreen" value="true" />
		<param name="allowscriptaccess" value="always" />
	</object>
</p>
EOT;
			}

			return $this->hashPart($rc, 'B');
		}
		else if ($matches[2] == 'embed:')
		{
			if (preg_match('#soundcloud.com/#', $url, $matches))
			{
				$data = wd_entities('http://player.soundcloud.com/player.swf');
				$flashvars = wd_entities('url=' . urlencode($url) . '&show_comments=true&color=F65FB8');

				$rc = <<<EOT
<p>
	<object height="81" width="100%" data="$data" type="application/x-shockwave-flash">
		<param name="movie" value="$data" />
		<param name="allowscriptaccess" value="always" />
		<param name="flashvars" value="$flashvars" />
	</object>
</p>
EOT;

				return $this->hashPart($rc, 'B');
			}
		}

		#
		#
		#

		$url = $this->encodeAmpsAndAngles($url);

		$result = "<a href=\"$url\"";
		if (isset($title)) {
			$title = str_replace('"', '&quot;', $title);
			$title = $this->encodeAmpsAndAngles($title);
			$result .=  " title=\"$title\"";
		}

		if (substr($url, 0, 7) == 'http://')
		{
			$result .= ' target="_blank"';
		}

		$link_text = $this->runSpanGamut($link_text);
		$result .= ">$link_text</a>";

		return $this->hashPart($result);
	}
}
