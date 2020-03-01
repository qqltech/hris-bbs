composer install
mkdir database/migrations/projects
cp database/migrations/__defaults/*.* database/migrations/projects
cp .env-example .env
chmod 777 .env
chmod 777 -R app/Models
chmod 777 -R public
chmod 777 -R storage
chmod 777 -R database/migrations