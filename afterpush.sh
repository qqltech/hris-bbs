if [ -d .git ]; then
    chmod 777 .gitignore
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