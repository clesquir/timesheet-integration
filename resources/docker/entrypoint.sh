#!/usr/bin/env bash

set -eu

app_environment=${APP_ENV:-prod}
install_dev_dependencies=${INSTALL_DEV_DEPENDENCIES:-false}

if [ "${install_dev_dependencies}" = true ]; then
	echo "Installing dev dependencies"
	composer install --optimize-autoloader --no-interaction --classmap-authoritative
fi

if [ "${app_environment}" == "prod" ]; then
	echo "Clear cache"
	/app/bin/console cache:clear

	chown -R www-data:www-data /app && \
	chown -R www-data:www-data /app # && \
fi

exec "$@"
