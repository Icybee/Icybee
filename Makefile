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
CSS_UNCOMPRESSED = assets/admin-uncompressed.css

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
	
JS_COMPRESSOR = php build/compress.php
JS_COMPRESSED = assets/admin.js
JS_UNCOMPRESSED = assets/admin-uncompressed.js

MOOTOOLS_FILES = \
	build/mootools-core.js \
	build/mootools-more.js

MOOTOOLS_COMPRESSED = assets/mootools.js
MOOTOOLS_UNCOMPRESSED = assets/mootools-uncompressed.js

all: $(JS_COMPRESSED) $(JS_UNCOMPRESSED) $(CSS_COMPRESSED) $(CSS_UNCOMPRESSED) $(MOOTOOLS_UNCOMPRESSED) $(MOOTOOLS_COMPRESSED)

$(JS_COMPRESSED): $(JS_UNCOMPRESSED)
	$(JS_COMPRESSOR) $^ >$@

$(JS_UNCOMPRESSED): $(JS_FILES)
	cat $^ >$@

$(CSS_COMPRESSED): $(CSS_FILES)
	$(CSS_COMPRESSOR) -x build/admin.less >$@

$(CSS_UNCOMPRESSED): $(CSS_FILES)
	$(CSS_COMPRESSOR) build/admin.less >$@
	
$(MOOTOOLS_UNCOMPRESSED): $(MOOTOOLS_FILES)
	cat $^ >$@

$(MOOTOOLS_COMPRESSED): $(MOOTOOLS_UNCOMPRESSED)
	$(JS_COMPRESSOR) $^ >$@

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
	rm -f $(CSS_COMPRESSED)
	rm -f $(CSS_UNCOMPRESSED)
	rm -f $(JS_COMPRESSED)
	rm -f $(JS_UNCOMPRESSED)
	rm -f $(MOOTOOLS_COMPRESSED)
	rm -f $(MOOTOOLS_UNCOMPRESSED)
	
	rm -f composer.phar
	rm -f composer.lock
	rm -Rf docs
	rm -Rf vendor