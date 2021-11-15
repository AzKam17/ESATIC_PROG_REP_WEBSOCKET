web: vendor/bin/heroku-php-apache2 public/
worker: php bin/console run:websocket-server
async: php bin/console messenger:consume async -vv