COMPOSER = ./build/composer.phar

test:
	@php ./test/Aplazame.php

dependencies:
	@$(COMPOSER) update --dev
