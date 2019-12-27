#!/bin/bash


if test $TRAVIS_TAG
then
    utils/make-package.sh pdnsmanager-${TRAVIS_TAG:1} ${TRAVIS_TAG:1}
    utils/make-package.sh pdnsmanager-$TRAVIS_COMMIT $TRAVIS_COMMIT
else
    utils/make-package.sh pdnsmanager-$TRAVIS_COMMIT $TRAVIS_COMMIT
fi

exit 0

