mkdir database/migrations/projects
mkdir database/migrations/alters
cp database/migrations/__defaults/*.* database/migrations/projects/
touch database/database.sqlite
chmod 777 .gitignore
chmod 777 .env
chmod 777 -R app/Models
chmod 777 -R app/Cores
chmod 777 -R public
chmod 777 -R resources/views/projects
chmod 777 -R storage
chmod 777 -R database/migrations