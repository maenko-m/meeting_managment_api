composer install

php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --no-interaction

exec php -S 0.0.0.0:8000 -t public