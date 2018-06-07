#!/bin/bash

curl -F "file=@pdnsmanager-$TRAVIS_TAG.tar.gz" -u "travis:$UPLOAD_PASS" 'https://upload.pdnsmanager.org/?action=release'