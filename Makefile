JS_COMPRESSOR = curl -X POST -s --data-urlencode 'input@$^' http://javascript-minifier.com/raw
#JS_COMPRESSOR = cat $^ # uncomment this line to produce uncompressed files

IDEPENDONYOU_JS = build/tmp/idependonyou.js
MOOTOOLS_JS = build/tmp/mootools.js
MOOTOOLS_MORE_JS = build/mootools-more.js

PAGE_JS = assets/page.js
PAGE_JS_UNCOMPRESSED = build/tmp/page-uncompressed.js
PAGE_JS_UNCOMPRESSED_FILES = \
	$(IDEPENDONYOU_JS) \
	$(MOOTOOLS_JS) \
	$(MOOTOOLS_MORE_JS) \
	../../icanboogie/icanboogie/assets/icanboogie.js

CSS_FILES = \
	build/admin.less \
	build/actionbar.less \
	build/alerts.less \
	build/checkbox-wrapper.less \
	build/forms.less \
	build/mixins.less \
	build/navigation.less \
	build/popover-image.less \
	build/reset.less \
	build/spinner.less \
	build/variables.less

CSS_COMPRESSOR = `which lessc`
CSS_COMPRESSED = assets/admin.css
CSS_UNCOMPRESSED = build/tmp/admin-uncompressed.css

JS_FILES = \
	build/string.js \
	build/admin.js \
	build/alerts.js \
	build/actionbar.js \
	build/forms.js \
	build/checkbox-wrapper.js \
	build/popover-image.js \
	build/reset.js \
	build/save-mode.js \
	build/spinner.js \
	build/widget.js

JS_COMPRESSED = assets/admin.js
JS_UNCOMPRESSED = build/tmp/admin-uncompressed.js

PAGE_JS_FILES = \
	$(IDEPENDONYOU_JS) \
	build/tmp/mootools-core.js \
	build/mootools-more.js

all: \
	$(PAGE_JS) \
	$(JS_COMPRESSED) \
	$(JS_UNCOMPRESSED) \
	$(CSS_COMPRESSED) \
	$(CSS_UNCOMPRESSED)

$(PAGE_JS): $(PAGE_JS_UNCOMPRESSED)
	$(JS_COMPRESSOR) >$@

$(PAGE_JS_UNCOMPRESSED): $(PAGE_JS_UNCOMPRESSED_FILES)
	cat $^ >$@

$(IDEPENDONYOU_JS):
	curl -o $@ https://raw.githubusercontent.com/olvlvl/IDependOnYou/master/idependonyou.js

$(MOOTOOLS_JS):
	curl -o $@ http://mootools.net/download/get/mootools-core-1.5.0-full-nocompat.js

#
#
#

$(JS_COMPRESSED): $(JS_UNCOMPRESSED)
	$(JS_COMPRESSOR) >$@

$(JS_UNCOMPRESSED): $(JS_FILES)
	cat $^ >$@

$(CSS_COMPRESSED): $(CSS_FILES)
	$(CSS_COMPRESSOR) -x build/admin.less >$@

$(CSS_UNCOMPRESSED): $(CSS_FILES)
	$(CSS_COMPRESSOR) build/admin.less >$@

composer.phar:
	@echo "Installing composer..."
	@curl -s https://getcomposer.org/installer | php

vendor: composer.phar
	@php composer.phar install --prefer-source --dev

test: vendor
	@phpunit

doc: vendor
	@mkdir -p "docs"

	@apigen \
	--source ./ \
	--destination docs/ --title Icybee \
	--exclude "*/composer/*" \
	--exclude "*/tests/*" \
	--template-config /usr/share/php/data/ApiGen/templates/bootstrap/config.neon

clean:
	rm -f build/tmp/*.js
	rm -f composer.phar
	rm -f composer.lock
	rm -Rf docs
	rm -Rf vendor