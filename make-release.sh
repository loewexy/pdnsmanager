#!/bin/bash

mkdir -p releases/

tar -czf releases/dpns-manager-$( git rev-parse HEAD | cut -c 1-10 ).tar.gz *.php LICENSE lib/ js/ include/ api/ config/config-default.php
