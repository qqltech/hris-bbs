CALL composer install
CALL md database/migrations/projects
CALL database/migrations/alters
CALL database/migrations/__defaults/*.* database/migrations/projects
CALL copy .env-example .env