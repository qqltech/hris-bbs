mkdir ../$1
ln -s app ../$1
ln -s bootstrap ../$1
ln -s config ../$1
ln -s database ../$1
ln -s resources ../$1
ln -s routes ../$1
ln -s storage ../$1
ln -s templates ../$1
ln -s tests ../$1
ln -s vendor ../$1
ln -s .htaccess ../$1
ln -s .styleci.yml ../$1
ln -s composer.json ../$1
ln -s composer.lock ../$1
ln -s phpunit.xml ../$1

cp -R public ../$1
cp .env ../$1