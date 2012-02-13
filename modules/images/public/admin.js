window.addEvent
(
	'domready', function()
	{
		var trigger = document.body.getElement('.group--resources-images-inject input[type="checkbox"]')
		, options
		, thumbnails
		, thumbnailsDescription = null;

		if (!trigger)
		{
			return;
		}

		options = document.body.getElement('.group--resources-images-inject-options');
		thumbnails = document.body.getElement('.group--resources-images-inject-thumbnails');

		if (thumbnails)
		{
			thumbnailsDescription = thumbnails.getPrevious('.form-section-description');
		}

		function check()
		{
			var display = trigger.checked ? 'block' : 'none';

			options.setStyle('display', display);

			if (thumbnails)
			{
				thumbnails.setStyle('display', display);

				if (thumbnailsDescription)
				{
					thumbnailsDescription.setStyle('display', display);
				}
			}
		}

		trigger.addEvent('change', check);

		check();
	}
);