#!/bin/bash


if test $TRAVIS_TAG
then
    utils/make-package.sh pdnsmanager-$TRAVIS_TAG
else
    utils/make-package.sh pdnsmanager-$TRAVIS_COMMIT
fi

exit 0

