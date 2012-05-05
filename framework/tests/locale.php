<?php

require '../../includes/startup.php';


$locale = WdLocale::get('fr');


$t = $locale->translator;

WdI18n::push_scope('madonna.label');

var_dump($t('display'), $t('Search'));

//var_dump($t->messages);

var_dump(t('Search'));


//exit;

$now = time();
$f = $date_formatter = $locale->date_formatter;


?>

<table border="1">
	<thead>
	<tr>
		<th>Field</th>
		<th>Sym.</th>
		<th>No.</th>
		<th>Example</th>
		<th>Description</th>
	</tr>
	</thead>

	<tbody>
	<!-- ERA -->

	<tr>
		<th rowspan="3">era</th>
		<td rowspan="3">G</td>
		<td>1..3</td>
		<td><?php echo $f($now, "GGG") ?></td>
		<td rowspan="3">Era - Replaced with the Era string for the current date. One to three letters for the abbreviated form, four letters for the long form, five for the narrow form.</td>
	</tr>

	<tr>
		<td>4</td>
		<td><?php echo $f($now, "GGGG") ?></td>
	</tr>

	<tr>
		<td>5</td>
		<td><?php echo $f($now, "GGGGG") ?></td>
	</tr>

	<!-- YEAR -->

	<tr>
		<th rowspan="3">year</th>
		<td>y</td>
		<td>1..n</td>
		<td><?php echo $f($now, "y") ?></td>
		<td>Year. Normally the length specifies the padding, but for two letters it also specifies the maximum length. Example:

			<table>
				<tr>
					<th>y</th>
					<th>yy</th>
					<th>yyy</th>
					<th>yyyy</th>
					<th>yyyyy</th>
				</tr>

				<tr>
					<td><?php echo $f($now, "y") ?></td>
					<td><?php echo $f($now, "yy") ?></td>
					<td><?php echo $f($now, "yyy") ?></td>
					<td><?php echo $f($now, "yyyy") ?></td>
					<td><?php echo $f($now, "yyyyy") ?></td>
				</tr>
			</table>
		</td>
	</tr>

	<tr>
		<td>Y</td>
		<td>1..n</td>
		<td><?php echo $f($now, "Y") ?></td>
		<td>Year (in "Week of Year" based calendars). This year designation is used in ISO year-week calendar as defined by ISO 8601, but can be used in non-Gregorian based calendar systems where week date processing is desired. May not always be the same value as calendar year.</td>
	</tr>

	<tr>
		<td>u</td>
		<td>1..n</td>
		<td><?php echo $f($now, "u") ?></td>
		<td>Extended year. This is a single number designating the year of this calendar system, encompassing all supra-year fields. For example, for the Julian calendar system, year numbers are positive, with an era of BCE or CE. An extended year value for the Julian calendar system assigns positive values to CE years and negative values to BCE years, with 1 BCE being year 0.</td>
	</tr>

	<!-- QUARTER -->

	<tr>
		<th rowspan="6">quarter</th>
		<td rowspan="3">Q</td>
		<td>1..2</td>
		<td><?php echo $f($now, 'QQ') ?></td>
		<td rowspan="3">Quarter - Use one or two for the numerical quarter, three for the abbreviation, or four for the full name.</td>
	</tr>

	<tr>
		<td>3</td>
		<td><?php echo $f($now, 'QQQ') ?></td>
	</tr>

	<tr>
		<td>4</td>
		<td><?php echo $f($now, 'QQQQ') ?></td>
	</tr>

	<tr>
		<td rowspan="3">q</td>
		<td>1..2</td>
		<td><?php echo $f($now, 'qq') ?></td>
		<td rowspan="3"><strong>Stand-Alone</strong> Quarter - Use one or two for the numerical quarter, three for the abbreviation, or four for the full name.</td>
	</tr>

	<tr>
		<td>3</td>
		<td><?php echo $f($now, 'qqq') ?></td>
	</tr>

	<tr>
		<td>4</td>
		<td><?php echo $f($now, 'qqqq') ?></td>
	</tr>

	<!-- MONTH -->

	<tr>
		<th rowspan="9">month</th>
		<td rowspan="4">M</td>
		<td>1..2</td>
		<td><?php echo $f($now, "MM") ?></td>
		<td rowspan="4">Month - Use one or two for the numerical month, three for the abbreviation, four for the full name, or five for the narrow name.</td>
	</tr>

	<tr>
		<td>3</td>
		<td><?php echo $f($now, "MMM") ?></td>
	</tr>

	<tr>
		<td>4</td>
		<td><?php echo $f($now, "MMMM") ?></td>
	</tr>

	<tr>
		<td>5</td>
		<td><?php echo $f($now, "MMMMM") ?></td>
	</tr>

	<tr>
		<td rowspan="4">L</td>
		<td>1..2</td>
		<td><?php echo $f($now, "LL") ?></td>
		<td rowspan="4"><strong>Stand-Alone</strong> Month - Use one or two for the numerical month, three for the abbreviation, four for the full name, or 5 for the narrow name.</td>
	</tr>

	<tr>
		<td>3</td>
		<td><?php echo $f($now, "LLL") ?></td>
	</tr>

	<tr>
		<td>4</td>
		<td><?php echo $f($now, "LLLL") ?></td>
	</tr>

	<tr>
		<td>5</td>
		<td><?php echo $f($now, "LLLLL") ?></td>
	</tr>

	<tr>
		<td>l</td>
		<td>1</td>
		<td><?php echo $f($now, 'l') ?></td>
		<td>Special symbol for Chinese leap month, used in combination with M. Only used with the Chinese calendar.</td>
	</tr>

	<!-- WEEK -->

	<tr>
		<th rowspan="2">week</th>
		<td>w</td>
		<td>1..2</td>
		<td><?php echo $f($now, 'w') ?></td>
		<td>Week of Year.</td>
	</tr>

	<tr>
		<td>W</td>
		<td>1</td>
		<td><?php echo $f($now, 'W') ?></td>
		<td>Week of Month.</td>
	</tr>

	<!-- DAY -->

	<tr>
		<th rowspan="4">day</th>
		<td>d</td>
		<td>1..2</td>
		<td><?php echo $f($now, 'd') ?></td>
		<td>Day of the month.</td>
	</tr>

	<tr>
		<td>D</td>
		<td>1..3</td>
		<td><?php echo $f($now, 'D') ?></td>
		<td>Day of year.</td>
	</tr>

	<tr>
		<td>F</td>
		<td>1</td>
		<td><?php echo $f($now, 'F') ?></td>
		<td>Day of Week in Month. The example is for the 2nd Wed in July.</td>
	</tr>

	<tr>
		<td>g</td>
		<td>1..n</td>
		<td><?php echo $f($now, 'g') ?></td>
		<td>Modified Julian day. This is different from the conventional Julian day number in two
		regards. First, it demarcates days at local zone midnight, rather than noon GMT. Second,
		it is a local number; that is, it depends on the local time zone. It can be thought of as
		a single number that encompasses all the date-related fields..</td>
	</tr>

	<!-- WEEK DAY -->

	<tr>
		<th rowspan="11">week day</th>
		<td rowspan="3">E</td>
		<td>1..3</td>
		<td><?php echo $f($now, 'E') ?></td>
		<td rowspan="3">Day of week - Use one through three letters for the short day, or four for the full name, or five for the narrow name.</td>
	</tr>

	<tr>
		<td>4</td>
		<td><?php echo $f($now, 'EEEE') ?></td>
	</tr>

	<tr>
		<td>5</td>
		<td><?php echo $f($now, 'EEEEE') ?></td>
	</tr>

	<tr>
		<td rowspan="4">e</td>
		<td>1..2</td>
		<td><?php echo $f($now, 'e') ?></td>
		<td rowspan="4">Local day of week. Same as E except adds a numeric value that will depend on the local starting day of the week, using one or two letters. For this example, Monday is the first day of the week.</td>
	</tr>

	<tr>
		<td>3</td>
		<td><?php echo $f($now, 'eee') ?></td>
	</tr>

	<tr>
		<td>4</td>
		<td><?php echo $f($now, 'eeee') ?></td>
	</tr>

	<tr>
		<td>5</td>
		<td><?php echo $f($now, 'eeeee') ?></td>
	</tr>

	<tr>
		<td rowspan="4">c</td>
		<td>1</td>
		<td><?php echo $f($now, 'c') ?></td>
		<td rowspan="4"><strong>Stand-Alone</strong> local day of week - Use one letter for the local numeric value (same as 'e'), three for the short day, or four for the full name, or five for the narrow name. </td>
	</tr>

	<tr>
		<td>3</td>
		<td><?php echo $f($now, 'ccc') ?></td>
	</tr>

	<tr>
		<td>4</td>
		<td><?php echo $f($now, 'cccc') ?></td>
	</tr>

	<tr>
		<td>5</td>
		<td><?php echo $f($now, 'ccccc') ?></td>
	</tr>

	<!-- PERIOD -->

	<tr>
		<th>period</th>
		<td>a</td>
		<td>1</td>
		<td><?php echo $f($now, 'a') ?></td>
		<td>AM or PM</td>
	</tr>

	<!-- HOUR -->

	<tr>
		<th rowspan="5">hour</th>
		<td>h</td>
		<td>1..2</td>
		<td><?php echo $f($now, 'h') ?></td>
		<td>Hour [0-12].</td>
	</tr>

	<tr>
		<td>H</td>
		<td>1..2</td>
		<td><?php echo $f($now, 'H') ?></td>
		<td>Hour [0-23].</td>
	</tr>

	<tr>
		<td>K</td>
		<td>1..2</td>
		<td><?php echo $f($now, 'K') ?></td>
		<td>Hour [0-11].</td>
	</tr>

	<tr>
		<td>k</td>
		<td>1..2</td>
		<td><?php echo $f($now, 'k') ?></td>
		<td>Hour [1-24].</td>
	</tr>

	<tr>
		<td>j</td>
		<td>1..2</td>
		<td>n/a</td>
		<td>This is a special-purpose symbol. It must not occur in pattern or skeleton data. Instead, it is reserved for use in APIs doing flexible date pattern generation. In such a context, it requests the preferred format (12 versus 24 hour) for the language in question, as determined by whether h, H, K, or k is used in the standard short time format for the locale, and should be replaced by h, H, K, or k before beginning a match against availableFormats data.</td>
	</tr>

	<!-- MINUTE -->

	<tr>
		<th>minute</th>
		<td>m</td>
		<td>1..2</td>
		<td><?php echo $f($now, 'm') ?></td>
		<td>Minute. Use one or two for zero padding.</td>
	</tr>

	<!-- SECOND -->

	<tr>
		<th rowspan="3">second</th>
		<td>s</td>
		<td>1..2</td>
		<td><?php echo $f($now, 's') ?></td>
		<td>Second. Use one or two for zero padding.</td>
	</tr>

	<tr>
		<td>S</td>
		<td>1..n</td>
		<td><?php echo $f($now, 'S') ?></td>
		<td>Fractional Second - truncates (like other time fields) to the count of letters. (example shows display using pattern SSSS for seconds value 12.34567)</td>
	</tr>

	<tr>
		<td>A</td>
		<td>1..n</td>
		<td><?php echo $f($now, 'A') ?></td>
		<td>Milliseconds in day. This field behaves exactly like a composite of all time-related fields, not including the zone fields. As such, it also reflects discontinuities of those fields on DST transition days. On a day of DST onset, it will jump forward. On a day of DST cessation, it will jump backward. This reflects the fact that is must be combined with the offset field to obtain a unique local time value.</td>
	</tr>


	</tbody>
</table>





<h3>Week of year</h3>

<pre><?php

echo $f($now, "'w: 'w'<br />ww: 'ww");

?></pre>

<h3>Week of month</h3>

<pre><?php echo $f($now, "'W: 'W"); ?></pre>


<h3>Day</h3>

<h4>Day of the month</h4>

<pre><?php echo $f($now, "'d: 'd'<br />dd: 'dd") ?></pre>

<h4>Day of year</h4>

<pre><?php echo $f($now, "'D: 'D'<br />DD: 'DD'<br />DDD: 'DDD") ?></pre>

<h4>Day of week in month</h4>

<?php








$a = $date_formatter->format($now, "HH:mm:ss zzzz Z EEEE d MMMM y");

var_dump($a);

$a = $date_formatter->format($now, "'medium: 'G 'wide: 'GGGG 'narrow: 'GGGGG");

var_dump($a);

$a = $date_formatter->format($now, "'day of the month: 'd', day of year: 'D DD', day of week in month: 'F', modified julian day: 'g");

echo '<pre>' . $a . '</pre>';

$a = $date_formatter->format($now, "'day of week: 'E EEEE EEEEE', local day of week: 'e ee eee eeee eeeee', stand-alone local day of week: 'c cccc ccccc");

echo '<pre>' . $a . '</pre>';

?>

<h3>Available formats</h3>

<pre><?php

$available_formats = $locale->conventions['dates']['dateTimeFormats']['availableFormats'];

foreach ($available_formats as $id => $pattern)
{
	printf("(<em>%-16s</em>) %-16s: " . $date_formatter->format($now, $pattern) . "<br />", $id, $pattern);
}

?></pre>