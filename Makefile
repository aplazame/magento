COMPOSER = ./build/composer.phar

test:
	@php ./test/Aplazame.php

syntax.checker:
	@find . -type f -name "*.php" -exec php -l "{}" \;

dependencies:
	@$(COMPOSER) update --dev

push:
	@git push origin HEAD

branch:
	@git checkout master
	@git pull origin master
	@git checkout -b $(branch)
