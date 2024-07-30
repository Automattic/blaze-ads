#!/usr/bin/env bash

set -e

WATCH_FLAG=false

while getopts ':w' OPTION; do
	case $OPTION in
		w)
		WATCH_FLAG=true
		shift
		;;
	esac
done

echo "Installing the test environment..."

docker-compose exec -u www-data wordpress \
	/var/www/html/wp-content/plugins/blaze-ads/bin/install-wp-tests.sh

if $WATCH_FLAG; then
	echo "Running the tests on watch mode..."

	# Change directory to Blaze Ads' root in order to have access to .phpunit-watcher.yml
	docker-compose exec -u www-data wordpress bash -c \
		"cd /var/www/html/wp-content/plugins/blaze-ads && \
		php -d xdebug.remote_autostart=on \
		./vendor/bin/phpunit-watcher watch --configuration ./phpunit.xml $*"
else
	echo "Running the tests..."

	docker-compose exec -u www-data wordpress \
		php -d xdebug.remote_autostart=on \
		/var/www/html/wp-content/plugins/blaze-ads/vendor/bin/phpunit \
		--configuration /var/www/html/wp-content/plugins/blaze-ads/phpunit.xml \
		$*
fi
