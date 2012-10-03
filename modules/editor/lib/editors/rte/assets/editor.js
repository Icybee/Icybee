!function() {

	function apply()
	{
		$$('textarea.moo').each
		(
			function(el)
			{
				if (el.retrieve('mooeditable'))
				{
					return;
				}

				var options = el.get('dataset');

				if (options.externalCss)
				{
					options.externalCSS = JSON.decode(options.externalCss);
				}

				if (options.baseUrl)
				{
					options.baseURL = options.baseUrl;
				}

				el.mooEditable(options);

				el.store('mooeditable', true);
			}
		);
	}

	document.addEvent('editors', apply); // TODO-20110123: remove 'domready' and 'editors'
	window.addEvent('brickrouge.update', apply);

} ()