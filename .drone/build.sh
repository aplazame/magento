#!/bin/bash

set -e 

# tests
# make test

case $DRONE_BRANCH in
    master)
        echo master, all checks have passed
        ;;
    *)
        echo *$DRONE_BRANCH* pull request, all checks have passed
        ;;
esac
