echo "$(cat gitignore.txt)" > .gitignore
if [ -d .git ]; then
else
  git init .
  git remote add origin $1
fi;
git add $2
git commit -m $3
git push origin master
# . git.sh https://gitlab.com/starlight93/testingfajar.git filename commitan