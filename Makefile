COMPOSER = ./build/composer.phar

test:
	@php ./test/Aplazame.php

syntax.checker:
	@find . -type f -name "*.php" -exec php -l "{}" \;

dependencies:
	@$(COMPOSER) update --dev
