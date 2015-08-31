#!/bin/bash

set -e

# tests
sudo apt-get install php5-cli
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
