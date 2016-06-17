#!/bin/bash

isphp7=`php travis/scripts/version.php`
if [ "$isphp7" -eq 0 ]; then
    phpenv config-add travis/etc/php-mongo.ini
    pecl install mongodb
fi

phpenv config-add travis/etc/php-mongodb.ini
