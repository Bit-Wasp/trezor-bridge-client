
_GENERATE_DIR=./src/

ifdef GENERATE_DIR
_GENERATE_DIR=$(GENERATE_DIR)
endif

phpcs: pretest
		vendor/bin/phpcs --standard=PSR2 -n src test/unit/

phpcbf: pretest
		vendor/bin/phpcbf --standard=PSR2 -n src test/unit/

pretest:
		if [ ! -d vendor ] || [ ! -f composer.lock ]; then composer install; else echo "Already have dependencies"; fi

phpunit-ci: pretest
		mkdir -p build
		php ${EXT_PHP} vendor/bin/phpunit --coverage-text --coverage-clover=build/coverage.clover

ifdef OCULAR_TOKEN
scrutinizer: ocular
		@php ocular.phar code-coverage:upload --format=php-clover build/coverage.clover --access-token=$(OCULAR_TOKEN);
else
scrutinizer: ocular
		php ocular.phar code-coverage:upload --format=php-clover build/coverage.clover;
endif

clean: clean-env clean-deps

clean-env:
		rm -rf ocular.phar
		rm -rf build

clean-deps:
		rm -rf vendor/
