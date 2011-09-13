window.addEvent
(
	'domready', function()
	{
		var trigger = document.getElement('#section-resources-images-inject input[type="checkbox"]');

		if (!trigger)
		{
			return;
		}

		var options = $('section-resources-images-inject-options');
		var thumbnails = $('section-resources-images-inject-thumbnails');

		if (thumbnails)
		{
			var thumbnails_description = thumbnails.getPrevious('.form-section-description');
		}

		function check()
		{
			var display = trigger.checked ? 'block' : 'none';

			options.setStyle('display', display);

			if (thumbnails)
			{
				thumbnails.setStyle('display', display);

				if (thumbnails_description)
				{
					thumbnails_description.setStyle('display', display);
				}
			}
		}

		trigger.addEvent('change', check);

		check();
	}
);