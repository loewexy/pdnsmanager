#!/bin/bash

cd backend/src
if ! composer install
then
    exit 1
fi
cd ../..

cd backend/test
if ! npm install
then
    exit 2
fi
cp ../../.travis/data/config-backend-test.sh config.sh
cd ../..

exit 0

