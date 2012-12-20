ADMIN_LESS = ./build/admin.less
ADMIN = ./assets/admin
ADMIN_UNCOMPRESSED = ./assets/admin-uncompressed
INCLUDE_PATH = --include-path="./framework/Brickrouge/lib"
LESS_COMPILER ?= `which lessc`
WATCHR ?= `which watchr`
DIRS = modules/editor/lib/editors/rte

build:
	@if test ! -z ${LESS_COMPILER}; then \
		lessc ${INCLUDE_PATH} ${ADMIN_LESS} > ${ADMIN_UNCOMPRESSED}.css; \
		lessc ${INCLUDE_PATH} -x ${ADMIN_LESS} > ${ADMIN}.css; \
		echo "Brickrouge successfully built! - `date`"; \
	else \
		echo "You must have the LESS compiler installed in order to build Brickrouge."; \
		echo "You can install it by running: npm install less -g"; \
	fi
	
	@cat ./build/admin.js ./build/actionbar.js ./build/widget.js ./build/spinner.js > ${ADMIN_UNCOMPRESSED}.js
	@php ./build/compress.php ${ADMIN_UNCOMPRESSED}.js ${ADMIN}.js;
	
	@set -e; for d in $(DIRS); do echo "Making $$d"; $(MAKE) -C $$d ; done

watch:
	echo "Watching less files..."
	watchr -e "watch('lib/.*\.less') { system 'make' }"

install:
	@if [ ! -f "composer.phar" ] ; then \
		echo "Installing composer..." ; \
		curl -s https://getcomposer.org/installer | php ; \
	fi
	
	@php composer.phar install

test:
	@if [ ! -d "vendor" ] ; then \
		make install ; \
	fi

	@phpunit

doc:
	@if [ ! -d "vendor" ] ; then \
		make install ; \
	fi

	@mkdir -p "docs"

	@apigen \
	--source ./ \
	--destination docs/ --title Icybee \
	--exclude "*/composer/*" \
	--exclude "*/tests/*" \
	--template-config /usr/share/php/data/ApiGen/templates/bootstrap/config.neon
	
clean:
	@rm -fR docs
	@rm -fR vendor
	@rm -f composer.lock
	@rm -f composer.phar

.PHONY: build watch