ADMIN_LESS = ./build/admin.less
ADMIN = ./assets/admin
ADMIN_UNCOMPRESSED = ./assets/admin-uncompressed
INCLUDE_PATH = --include-path="./framework/Brickrouge/lib"
LESS_COMPILER ?= `which lessc`
WATCHR ?= `which watchr`

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
	php ./framework/Brickrouge/build/compress.php ${ADMIN_UNCOMPRESSED}.js ${ADMIN}.js;

phar:
	@php -d phar.readonly=0 ./build/phar.php;

watch:
	echo "Watching less files..."
	watchr -e "watch('lib/.*\.less') { system 'make' }"

.PHONY: build watch
