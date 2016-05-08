!function (Brickrouge) {

	var devicePixelRatio = window.devicePixelRatio
	, loaders = []

	function loaderFrom(img) {

		var uid = Brickrouge.uidOf(img)

		if (uid in loaders) {
			return loaders[uid]
		}

		var loader = loaders[uid] = document.createElement('img')

		loader.onload = function () {

			img.src = loader.src

		}

		return loader
	}

	function update (img)
	{
		var aW = img.getAttribute('width')
		, aH = img.getAttribute('height')
		, prW = Math.round(aW * devicePixelRatio)
		, prH = Math.round(aH * devicePixelRatio)

		if (!aW && !aH) {
			return
		}

		if ((aW && prW == img.naturalWidth) || (aH && prH == img.naturalHeight)) {
			return
		}

		var loader = loaderFrom(img)
		, src = img.src.replace(/(\?|&)pixel\-ratio=[^&]+/, '')

		loader.src = src + (src.indexOf('?') === -1 ? '?' : '&') + 'pixel-ratio=' + devicePixelRatio
	}

	function updateFragment (fragment)
	{
		Array.prototype.forEach.call(fragment.querySelectorAll('img'), update)
	}

	window.addEventListener('load', function () {

		Brickrouge.observe(Brickrouge.EVENT_UPDATE, ev => {

			const fragment = ev.fragment

			if (fragment.tagName === 'IMG')
			{
				update(fragment)
			}
			else
			{
				updateFragment(fragment)
			}

		})

		setInterval(function () {

			if (devicePixelRatio !== window.devicePixelRatio) {
				devicePixelRatio = window.devicePixelRatio

				updateFragment(document.body)
			}

		}, 1000)

		updateFragment(document.body)

	})

} (Brickrouge);
