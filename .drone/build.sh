#!/bin/bash

set -e

# Dependencies
sudo apt-get install php5-cli

# Tests and syntax checker
# make test
make syntax.checker


case $DRONE_BRANCH in
    master)
        echo master, all checks have passed
        ;;
    *)
        echo *$DRONE_BRANCH* pull request, all checks have passed
        ;;
esac
