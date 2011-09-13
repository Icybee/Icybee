window.addEvent
(
	'domready', function()
	{
		new DatePicker
		(
			'input.date',
			{
				format: 'd/m/Y',
				inputOutputFormat: 'Y-m-d',
				debug: false
			}
		);
		
		new DatePicker
		(
			'input.datetime',
			{
				format: 'd/m/Y à H:i',
				inputOutputFormat: 'Y-m-d H:i:s',
				debug: false,
				timePicker: true
			}
		);
	}
);