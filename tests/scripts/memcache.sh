#!/bin/sh -e

if [ $(phpenv version-name) = "7.0" ]; then
  echo "skipping memcache on php 7"
else
  mkdir -p ~/.phpenv/versions/$(phpenv version-name)/etc
  echo "extension=memcache.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini
fi