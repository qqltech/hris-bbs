echo "$(cat gitignore.txt)" > .gitignore
echo "masuk $1 $2 $3";
if [ -d .git ]; then
  echo "masuk $1 $2 $3";
else
  echo "$1 $2 $3";
  git init .
  git remote add origin $1
fi;
git add $2
git commit -m $3
git push origin master
echo git ok
# . git.sh https://gitlab.com/starlight93/testingfajar.git filename commitan