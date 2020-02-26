composer install
cp .env-example .env
chmod 777 .env
chmod 777 -R app/Models
chmod 777 -R public
chmod 777 -R storage
chmod 777 -R database/migrations