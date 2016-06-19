JS_COMPRESSOR = `which uglifyjs` $^ \
	--compress \
	--mangle \
	--screw-ie8 \
	--source-map $@.map
#JS_COMPRESSOR = cat $^ # uncomment this line to produce uncompressed files
CSS_COMPILER = `which sass`
CSS_COMPILER_OPTIONS = --style compressed   # comment to disable compression

IDEPENDONYOU_JS = build/tmp/idependonyou.js
MOOTOOLS_JS = build/tmp/mootools.js
MOOTOOLS_MORE_JS = src/assets/page/mootools-more.js
MOOTOOLS_CORE_VER = 1.6.0

ADMIN_CSS = ./assets/admin.css
ADMIN_CSS_FILES = $(shell ls src/assets/admin/*.scss)
ADMIN_CSS_ENTRY = ./src/assets/admin/main.scss

PAGE_JS = assets/page.js
PAGE_JS_UNCOMPRESSED = build/tmp/page-uncompressed.js
PAGE_JS_UNCOMPRESSED_FILES = \
	$(IDEPENDONYOU_JS) \
	$(MOOTOOLS_JS) \
	$(MOOTOOLS_MORE_JS) \
	../../icanboogie/icanboogie/assets/icanboogie.js

JS_FILES = \
	src/assets/admin/core.js \
	src/assets/admin/string.js \
	src/assets/admin/request.js \
	src/assets/admin/admin.js \
	src/assets/admin/alert.js \
	src/assets/admin/actionbar.js \
	src/assets/admin/form.js \
	src/assets/admin/popover-image.js \
	src/assets/admin/reset.js \
	src/assets/admin/save-mode.js \
	src/assets/admin/spinner.js \
	src/assets/admin/widget.js \
	src/assets/admin/adjust.js \
	src/assets/admin/pop-adjust.js \
	src/assets/admin/img-dpr.js

JS_COMPRESSED = assets/admin.js
JS_UNCOMPRESSED = build/tmp/admin-uncompressed.js

PAGE_JS_FILES = \
	$(IDEPENDONYOU_JS) \
	build/tmp/mootools-core.js \
	$(MOOTOOLS_MORE_JS)

all: \
	$(PAGE_JS) \
	$(JS_COMPRESSED) \
	$(JS_UNCOMPRESSED) \
	$(ADMIN_CSS)

$(PAGE_JS): $(PAGE_JS_UNCOMPRESSED)
	$(JS_COMPRESSOR) >$@

$(PAGE_JS_UNCOMPRESSED): $(PAGE_JS_UNCOMPRESSED_FILES)
	cat $^ >$@

$(IDEPENDONYOU_JS):
	mkdir -p build/tmp
	curl -o $@ https://raw.githubusercontent.com/olvlvl/IDependOnYou/master/idependonyou.js

$(MOOTOOLS_JS):
	mkdir -p build/tmp
	curl -o $@ https://raw.githubusercontent.com/mootools/mootools-core/$(MOOTOOLS_CORE_VER)/dist/mootools-core.js

#
#
#

$(JS_COMPRESSED): $(JS_UNCOMPRESSED)
	$(JS_COMPRESSOR) >$@

$(JS_UNCOMPRESSED): $(JS_FILES)
	cat $^ >$@

$(ADMIN_CSS): $(ADMIN_CSS_FILES)
	$(CSS_COMPILER) $(CSS_COMPILER_OPTIONS) $(ADMIN_CSS_ENTRY):$@

watch-css:
	echo "Watching SCSS files..."
	$(CSS_COMPILER) $(CSS_COMPILER_OPTIONS) --watch $(ADMIN_CSS_ENTRY):$(ADMIN_CSS)

#
#
#

vendor:
	@composer install

update:
	@composer update

autoload:
	@composer dump-autoload

test: vendor
	@phpunit

test-coverage: vendor
	@mkdir -p build/coverage
	@phpunit --coverage-html build/coverage

doc: vendor
	@mkdir -p build/docs
	@apigen \
	--source ./lib \
	--destination build/docs/ --title Icybee \
	--template-config /usr/share/php/data/ApiGen/templates/bootstrap/config.neon

clean:
	rm -Rf build
	rm -f  composer.lock
	rm -Rf vendor
