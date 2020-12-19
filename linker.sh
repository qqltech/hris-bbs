mkdir ../$1
ln -s `realpath database` ../$1
ln -s `realpath resources` ../$1
ln -s `realpath templates` ../$1
ln -s `realpath tests` ../$1
ln -s `realpath vendor` ../$1
ln -s `realpath routes` ../$1

cp -R bootstrap ../$1
cp -R public ../$1
cp -R config ../$1
cp -R storage ../$1
cp -R app ../$1
rm -rf  ../$1/app/Models/Additionals
ln -s `realpath app/Models/Additionals` ../$1/app/Models/Additionals
rm -rf  ../$1/app/Models/CustomModels
ln -s `realpath app/Models/CustomModels` ../$1/app/Models/CustomModels
rm -rf  ../$1/app/Models/Defaults
ln -s `realpath app/Models/Defaults` ../$1/app/Models/Defaults
# rm -rf ../$1/app/Models
# ln -s `realpath app/Models` ../$1/app/Models
rm -rf ../$1/app/Http/Controllers
ln -s `realpath app/Http/Controllers` ../$1/app/Http/Controllers
rm -rf ../$1/app/Helpers
ln -s `realpath app/Helpers` ../$1/app/Helpers

cp .env ../$1
cp .htaccess ../$1
cp composer.json ../$1
cp composer.lock ../$1
cp .styleci.yml ../$1
cp artisan ../$1
cp phpunit.xml ../$1

chmod 777 ../$1/.env
chmod 777 -R ../$1/public
chmod 777 -R ../$1/storage
