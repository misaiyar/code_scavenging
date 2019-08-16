#!/bin/sh
ls_date=`date +%Y%m%d%H%M`
branch=$(git branch | sed -n -e 's/^\* \(.*\)/\1/p')
repository=$(git remote -v|sed -n -e 's/.*\/\(.*\)\.git (fetch)/\1/p')
git archive -o $repository"_"$branch"_$1_$2_$ls_date.zip" $2 $(git diff --diff-filter=ACMR --name-only $1 $2)
git diff --diff-filter=ACMR --name-only $1 $2 > $repository"_"$branch"_$1_$2_$ls_date.list"
