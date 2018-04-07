
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
		if [ ! -d build ]; then mkdir build; fi

phpunit-ci-unit: pretest
		php vendor/bin/phpunit -c phpunit.xml --coverage-text --coverage-clover=build/coverage.clover

phpunit-ci-integration: pretest
		php vendor/bin/phpunit -c phpunit.device.xml --coverage-text --coverage-clover=build/coverage.clover

ocular:
		if [ ! -f ocular.phar ]; then wget https://scrutinizer-ci.com/ocular.phar; fi

scrutinizer: ocular
		php ocular.phar code-coverage:upload --format=php-clover build/coverage.clover

clean: clean-env clean-deps

clean-env:
		rm -rf ocular.phar
		rm -rf build

clean-deps:
		rm -rf vendor/
