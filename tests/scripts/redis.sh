#!/bin/sh -e

mkdir -p ~/.phpenv/versions/$(phpenv version-name)/etc
echo "extension=redis.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/conf.d/travis.ini