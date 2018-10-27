style.req:
	@composer install --no-interaction --quiet --ignore-platform-reqs

style:
	@vendor/bin/php-cs-fixer fix -v
