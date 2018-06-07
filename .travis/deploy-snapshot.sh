#!/bin/bash

curl -F "file=@pdnsmanager-$TRAVIS_COMMIT.tar.gz" -u "travis:$UPLOAD_PASS" 'https://upload.pdnsmanager.org/?action=snapshot'