<?php

// http://unicode.org/Public/cldr/1.9.0/
// http://unicode.org/reports/tr35/

//$path = '/home/olivier/Bureau/common/main/';
$path = '/Users/serveurweb/Sites/-unicode/common/main/';

$convention = array();

function build_convention($id)
{
	global $path;

	list($language, $territory) = explode('_', $id) + array(1 => null);

	if ($territory)
	{
		build_convention($language);
	}
	else if ($language != 'root')
	{
		build_convention('root');
	}

	echo "<tt><strong>build:</strong> $id<tt><br />";

	$xml = file_get_contents($path . $id . '.xml');

	//$xml = preg_replace('#<([^\s]+).+draft\=\"unconfirmed[^>]+>.+</\1>#', '', $xml);

	$tree = simplexml_load_string($xml);

	return traverse_xmlelement($tree);
}

function get_filtered_attributes($el)
{
	$attributes = $el->attributes();

	if (isset($attributes['draft']) && $attributes['draft'] == 'unconfirmed')
	{
		return;
	}

	return $attributes;
}

function from_camel_case($str)
{
	static $callback;

	return $str;

	if (!$callback)
	{
		$callback = create_function('$c', 'return "_" . strtolower($c[1]);');
	}

	if (!preg_match('#[^A-Z0-1]+#', $str))
	{
		return strtolower($str);
	}

	$str[0] = strtolower($str[0]);

    return preg_replace_callback('/([A-Z])/', $callback, $str);
}

function to_array_path($str)
{
	$str = from_camel_case($str);

	return substr(str_replace('/', "']['", $str) . "']", 2);
}

$aliases = array();
$aliases_path = array();

function traverse_xmlelement($el, $path=null, $parents=array(), $from_alias=null)
{
	global $convention;
	global $aliases, $aliases_path;

	if ($el->attributes()->draft)
	{
		return;
	}

	static $skip = array
	(
		'/fallback',
		'/identity',
		'/layout',
		'/posix',
		'/references',

		// more

		//'/localeDisplayNames/languages',
		//'/localeDisplayNames/territories',
		'/localeDisplayNames/scripts',
		'/localeDisplayNames/variants',
		'/localeDisplayNames/types',
		'/dates/timeZoneNames',
		//'/numbers/currencies',


		/*
		// debug

		'/dates/timeZoneNames',
		'/localeDisplayNames',
		'/numbers',
		'/dates/dateTimeFormats',
		'/dates/fields',
		'/units'
		*/
	);

	static $warzone = array
	(
		'/dates/calendars' => "calendar[@type='gregorian']",

		'/dates/dateFormats/full' => "dateFormat/pattern",
		'/dates/dateFormats/long' => "dateFormat/pattern",
		'/dates/dateFormats/medium' => "dateFormat/pattern",
		'/dates/dateFormats/short' => "dateFormat/pattern",

		'/dates/timeFormats/full' => "timeFormat/pattern",
		'/dates/timeFormats/long' => "timeFormat/pattern",
		'/dates/timeFormats/medium' => "timeFormat/pattern",
		'/dates/timeFormats/short' => "timeFormat/pattern",

		'/dates/dateTimeFormats/full' => "dateTimeFormat/pattern",
		'/dates/dateTimeFormats/long' => "dateTimeFormat/pattern",
		'/dates/dateTimeFormats/medium' => "dateTimeFormat/pattern",
		'/dates/dateTimeFormats/short' => "dateTimeFormat/pattern",

		//'/numbers/decimalFormats/decimalFormatLength' => "decimalFormat/pattern",
		//'/numbers/decimalFormats/short' => "decimalFormat"
	);

	static $reroute = array
	(
		'/dates/calendars' => '/dates',
		'/dates/eras/eraNames' => '/dates/eras/wide',
		'/dates/eras/eraAbbr' => '/dates/eras/abbreviated',
		'/dates/eras/eraNarrow' => '/dates/eras/narrow',
		//'/numbers/decimalFormats/decimalFormatLength' => '/numbers/decimalFormats/pattern'
	);

	if (in_array($path, $skip))
	{
		return;
	}

//	echo "<tt><strong>current path:</strong> $path</tt><br />";

	if (isset($warzone[$path]))
	{
		list($el) = $el->xpath($warzone[$path]);

		echo "<tt style=\"color: #ccc\"><strong>warpzone:</strong> $path := $warzone[$path]</tt><br />";
	}

	if (isset($reroute[$path]))
	{
		echo "<tt style=\"color: #ccc\"><strong>rewrite:</strong> $path := $reroute[$path]</tt><br />";

		$path = $reroute[$path];
	}

	if ($el->alias)
	{
		$source = (string) $el->alias->attributes()->source;
		$xpath = (string) $el->alias->attributes()->path;

		echo "<tt style=\"color: #ccc\"><strong>alias:</strong> $path := $xpath (in <em>$source</em>)</tt><br />";

		$i = 0;
		$target = $el;
		$relative_xpath = $xpath;
		$target_path = $path;

		while (substr($relative_xpath, 0, 3) == '../')
		{
			$relative_xpath = substr($relative_xpath, 3);
			$target_path = dirname($target_path);
			$target = $parents[$i++];
		}

		$child_path = substr($path, strlen($target_path));

		$aliases[$target_path][$child_path] = array($relative_xpath, $xpath);

		echo "<tt style=\"color: green\"><strong>alias: parent path</strong> $target_path := ($child_path) $relative_xpath</tt><br />";

		list($el) = $target->xpath($relative_xpath);

		if (!$el)
		{
			echo "<tt style=\"color: red\"><strong>alias not found:</strong> $path := $aliases[$path]</tt><br />";

			var_dump($el, $parents);

			return;
		}

		traverse_xmlelement($el, $path, $parents, true);

		return;
	}

	if (isset($aliases[$path]))
	{
		echo "<tt style=\"color: blue\"><strong>elements have aliases in path:</strong> $path</tt><br />";

		foreach ($aliases[$path] as $child_path => $x)
		{
			list($relative_xpath, $xpath) = $x;

			$target = $el->xpath($relative_xpath);

			if (!$target)
			{
				echo "<tt style=\"color: red\"><strong>xpath target not found:</strong> $relative_xpath (original: $xpath), path: $path, for child: $child_path</tt><br />";

				continue;
			}

			echo "<tt style=\"color: blue\"><strong>reuse alias:</strong> $relative_xpath, for element: $child_path</tt><br />";

			list($target) = $target;

			traverse_xmlelement($target, $path . $child_path, array_merge(array($el), $parents), true);
		}
	}

	$children = $el->children();

	if (count($children))
	{
		foreach ($children as $path_fragment => $child)
		{
			switch ($path . '/' . $path_fragment)
			{
				case '/characters/exemplarCharacters':
				case '/characters/ellipsis':
				{
					$key = (string) $child->attributes()->type;

					if (!$key)
					{
						$key = '0';
					}

					$path_fragment .= '/' . $key;
				}
				break;

				case '/dates/dateTimeFormats/availableFormats/dateFormatItem':
				case '/dates/dateTimeFormats/intervalFormats/intervalFormatItem':
				{
					$path_fragment = (string) $child->attributes()->id;
				}
				break;

				case '/dates/dateTimeFormats/appendItems/appendItem':
				{
					$path_fragment = (string) $child->attributes()->request;
				}
				break;

				case '/dates/fields/day/relative':
				{
					$path_fragment .= '/' . (string) $child->attributes()->type;
				}
				break;

				default:
				{
					if ($path_fragment == 'greatestDifference')
					{
						$path_fragment = (string) $child->attributes()->id;
					}
					else if ($path_fragment == 'unitPattern')
					{
						$attributes = $child->attributes();

						$key = (string) $attributes->count;
						$alt = (string) $attributes->alt;

						if (!$key)
						{
							$key = '0';
						}

						$path_fragment = $key;

						if ($alt)
						{
							$path_fragment = $alt . '/' . $path_fragment;
						}
					}
					else
					{
						$key = $child->attributes()->type;

						if ($key !== null)
						{
							$path_fragment = (string) $key;
						}
					}
				}
				break;
			}

			traverse_xmlelement($child, $path . '/' . $path_fragment, array_merge(array($el), $parents));
		}
	}
	else
	{
		$attributes = $el->attributes();
		$value = $attributes->choice ? (string) $attributes->choice : (string) $el;
		$array_path = to_array_path($path);

		//echo "<tt>\$convention{$array_path} := $value</tt><br />";

		eval("\$convention{$array_path} = \$value;");
	}
}

$id = 'fr';

build_convention($id);

ini_set('xdebug.var_display_max_depth', 10);
ini_set('xdebug.var_display_max_children', 10);

//var_dump($aliases);
var_dump($convention);

function encode($var, $pad='')
{
    if (is_array($var))
    {
        $code = '';

        foreach ($var as $key => $value)
        {
       		$code .= "\t$pad" . (is_numeric($key) ? "$key=>" : "'$key'=>") . encode($value, "\t$pad") . ",\n";
        }

        return "array\n$pad(\n" . substr($code, 0, -2) . "\n$pad)";
    }
    else
    {
    	if (is_numeric($var))
    	{
    		return $var;
    	}
    	else if (is_string($var))
        {
            return "'" . addslashes($var) . "'";
        }
        elseif (is_bool($code))
        {
            return ($code ? 'true' : 'false');
        }
        else
        {
            return 'null';
        }
    }
}

$export = "<?php

/*
**

CONVERTED AUTOMATICALLY

**
*/

return " . encode($convention) . ';';

file_put_contents(__DIR__ . "/conventions/$id.php", $export);

?>

<textarea cols="72" rows="32"><?php echo htmlentities($export, ENT_COMPAT, 'utf-8') ?></textarea>