<?php

/*

http://en.wikipedia.org/wiki/Time_zone
http://en.wikipedia.org/wiki/List_of_time_zones_by_UTC_offset
http://en.wikipedia.org/wiki/Daylight_saving_time

*/

$list = array
(
	-12.00,
	-11.00,
	-10.00,
	-09.50,
	-09.00,
	-08.00,
	-07.00,
	-06.00,
	-05.00,
	-04.50,
	-04.00,
	-03.50,
	-03.00,
	-02.00,
	-01.00,
	+00.00,
	+01.00,
	+02.00,
	+03.00,
	+03.50,
	+04.00,
	+04.50,
	+05.00,
	+05.50,
	+05.75,
	+06.00,
	+06.50,
	+07.00,
	+08.00,
	+09.00,
	+09.50,
	+10.00,
	+10.50,
	+11.00,
	+11.50,
	+12.00,
	+12.75,
	+13.00,
	+14.00
);

$time = time();
date_default_timezone_set("UTC");

foreach ($list as $offset)
{
	echo date('Y-m-d H:i:s', $time + ($offset * 60 * 60)) . "[$offset]<br />";
}