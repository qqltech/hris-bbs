mkdir ../$1
ln -s `realpath app` ../$1
ln -s `realpath bootstrap` ../$1
ln -s `realpath config` ../$1
ln -s `realpath database` ../$1
ln -s `realpath resources` ../$1
ln -s `realpath routes` ../$1
ln -s `realpath storage` ../$1
ln -s `realpath templates` ../$1
ln -s `realpath tests` ../$1
ln -s `realpath vendor` ../$1
ln -s `realpath .htaccess` ../$1
ln -s `realpath .styleci.yml` ../$1
ln -s `realpath composer.json` ../$1
ln -s `realpath composer.lock` ../$1
ln -s `realpath phpunit.xml` ../$1


cp -R public ../$1
cp .env ../$1