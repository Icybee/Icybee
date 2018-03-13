!function (Brickrouge) {

	let devicePixelRatio = window.devicePixelRatio
	const loaders = []

	function loaderFrom(img) {
		const uid = Brickrouge.uidOf(img)

		if (uid in loaders) {
			return loaders[uid]
		}

		const loader = loaders[uid] = document.createElement('img')

		loader.onload = _ => img.src = loader.src

		return loader
	}

	function update(img) {
		const aW = img.getAttribute('width')
		const aH = img.getAttribute('height')
		const prW = Math.round(aW * devicePixelRatio)
		const prH = Math.round(aH * devicePixelRatio)

		if (!aW && !aH) {
			return
		}

		if ((aW && prW == img.naturalWidth) || (aH && prH == img.naturalHeight)) {
			return
		}

		const loader = loaderFrom(img)
		const src = img.src.replace(/(\\?|&)(device-pixel-ratio|dpr)=[^&]+/, '')

		loader.src = src + (src.indexOf('?') === -1 ? '?' : '&') + 'dpr=' + devicePixelRatio
	}

	function updateFragment(fragment) {
		Array.prototype.forEach.call(fragment.querySelectorAll('img'), update)
	}

	window.addEventListener('load', _ => {
		Brickrouge.observeUpdate(ev => {
			const fragment = ev.fragment

			if (fragment.tagName === 'IMG') {
				update(fragment)
			} else {
				updateFragment(fragment)
			}
		})

		setInterval(_ => {
			if (devicePixelRatio !== window.devicePixelRatio) {
				devicePixelRatio = window.devicePixelRatio

				updateFragment(document.body)
			}
		}, 1000)

		updateFragment(document.body)
	})

}(Brickrouge);
