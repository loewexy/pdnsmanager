#!/bin/bash

curl -F "file=@pdnsmanager-${TRAVIS_TAG:1}.tar.gz" -u "travis:$UPLOAD_PASS" "https://upload.pdnsmanager.org/?action=release&version=${TRAVIS_TAG:1}"