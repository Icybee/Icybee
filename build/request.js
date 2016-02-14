/*
 * The Request.Element class requires the Request.API class provided by ICanBoogie.
 */

if (Request.API)
{
	/**
	 * Extends Request.API to support the loading of single HTML elements.
	 */
	Request.Element = new Class({

		Extends: Request.API,

		onSuccess: function(response, text)
		{
			var el = Elements.from(response.rc).shift()

			if (!response.assets)
			{
				this.parent(el, response, text)

				return
			}

			Brickrouge.updateAssets(response.assets, function() {

				this.fireEvent('complete', [ response, text ]).fireEvent('success', [ el, response, text ]).callChain()

			}.bind(this))
		}
	})

	/**
	 * Extends Request.Element to support loading of single widgets.
	 */
	Request.Widget = new Class({

		Extends: Request.Element,

		initialize: function(cl, onSuccess, options)
		{
			if (options == undefined)
			{
				options = {}
			}

			options.url = 'widgets/' + cl
			options.onSuccess = onSuccess

			this.parent(options)
		}
	})
}
