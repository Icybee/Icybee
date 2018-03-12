PACKAGE_NAME = icybee/icybee
PACKAGE_VERSION = 4.0
PHPUNIT_VERSION = phpunit-5.7.phar
PHPUNIT_FILENAME = build/$(PHPUNIT_VERSION)
PHPUNIT = php $(PHPUNIT_FILENAME)

JS_COMPRESSOR = `which uglifyjs` $^ \
	--compress \
	--mangle \
	--screw-ie8 \
	--source-map "filename='$@.map'" \
	--output "$@"
#JS_COMPRESSOR = cat $^ # uncomment this line to produce uncompressed files
CSS_COMPILER = `which sass`
CSS_COMPILER_OPTIONS = --style compressed   # comment to disable compression

IDEPENDONYOU_JS = build/tmp/idependonyou.js

MOOTOOLS_JS = build/tmp/mootools.js
MOOTOOLS_MORE_JS = src/assets/page/mootools-more.js
MOOTOOLS_CORE_VER = 1.6.0

BOOTSTRAP_CSS = assets/bootstrap.css
BOOTSTRAP_BRANCH = v4-dev

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
	src/assets/admin/Spinner.js \
	src/assets/admin/AdjustPopover.js \
	src/assets/admin/Adjust.js \
	src/assets/admin/img-dpr.js

JS_COMPRESSED = assets/admin.js
JS_UNCOMPRESSED = build/tmp/admin-uncompressed.js

PAGE_JS_FILES = \
	$(IDEPENDONYOU_JS) \
	build/tmp/mootools-core.js \
	$(MOOTOOLS_MORE_JS)

all: \
	$(PHPUNIT_FILENAME) \
	$(PAGE_JS) \
	$(JS_COMPRESSED) \
	$(JS_UNCOMPRESSED) \
	$(BOOTSTRAP_CSS) \
	$(ADMIN_CSS) \
	vendor

$(PAGE_JS): $(PAGE_JS_UNCOMPRESSED)
	$(JS_COMPRESSOR)

$(PAGE_JS_UNCOMPRESSED): $(PAGE_JS_UNCOMPRESSED_FILES)
	cat $^ >$@

$(IDEPENDONYOU_JS):
	mkdir -p build/tmp
	curl -o $@ https://raw.githubusercontent.com/olvlvl/IDependOnYou/master/idependonyou.js

$(MOOTOOLS_JS):
	mkdir -p build/tmp
	curl -o $@ https://raw.githubusercontent.com/mootools/mootools-core/$(MOOTOOLS_CORE_VER)/dist/mootools-core.js

$(BOOTSTRAP_CSS):
	mkdir -p build/tmp
	curl -o $@ https://raw.githubusercontent.com/twbs/bootstrap/$(BOOTSTRAP_BRANCH)/dist/css/bootstrap.min.css

#
#
#

$(JS_COMPRESSED): $(JS_UNCOMPRESSED)
	$(JS_COMPRESSOR)

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
	@COMPOSER_ROOT_VERSION=$(PACKAGE_VERSION) composer install

update:
	@COMPOSER_ROOT_VERSION=$(PACKAGE_VERSION) composer update

autoload: vendor
	@composer dump-autoload

$(PHPUNIT_FILENAME):
	mkdir -p build
	wget https://phar.phpunit.de/$(PHPUNIT_VERSION) -O $(PHPUNIT_FILENAME)

test: all
	@$(PHPUNIT)

test-coverage: all
	@mkdir -p build/coverage
	@$(PHPUNIT) --coverage-html ../build/coverage

test-coveralls: all
	@mkdir -p build/logs
	COMPOSER_ROOT_VERSION=$(PACKAGE_VERSION) composer require satooshi/php-coveralls
	@$(PHPUNIT) --coverage-clover ../build/logs/clover.xml
	php vendor/bin/coveralls -v

doc: vendor
	@mkdir -p build/docs
	@apigen generate \
	--source lib \
	--destination build/docs/ \
	--title "$(PACKAGE_NAME) v$(PACKAGE_VERSION)" \
	--template-theme "bootstrap"

clean:
	@rm -fR build
	@rm -fR vendor
	@rm -f composer.lock

.PHONY: all autoload doc clean test test-coverage test-coveralls update
