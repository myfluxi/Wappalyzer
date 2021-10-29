#!/bin/bash

REMOTE_URL="https://github.com/AliasIO/wappalyzer"
REMOTE_REPO="aliasio"
REMOTE_BRANCH="master"

git remote add $REMOTE_REPO $REMOTE_URL > /dev/null 2>&1
git fetch $REMOTE_REPO
git merge --strategy-option=theirs $REMOTE_REPO/$REMOTE_BRANCH

rm -rf icons
mv src/drivers/webextension/images/icons icons

git reset
git add icons
git add src/technologies
git add src/categories.json
git add src/groups.json

git commit -m $(git describe $REMOTE_REPO/$REMOTE_BRANCH --abbrev=0 --tags)
git clean -f -d --exclude="vendor"
git reset --hard