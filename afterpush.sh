chmod 777 .gitignore
if [ -d .git ]; then
    mv gitignore.txt .gitignore
    git add .
    git commit -m '[Fajar Maintain Kode]'
    git push origin master
    echo 'git push dijalankan'
fi
# else
#   git add .
#   git commit -m '[Fajar Maintain Kode]'
# fi;