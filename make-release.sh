#!/bin/bash
mkdir -p releases/
tar -czf releases/pdns-manager-$( git describe | cut -c 2- ).tar.gz *.php LICENSE lib/ js/ include/ api/ config/config-default.php
