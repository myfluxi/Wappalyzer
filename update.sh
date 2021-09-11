#!/bin/bash

git clone git@github.com:AliasIO/wappalyzer.git
rm -rf icons/*
cp wappalyzer/src/drivers/webextension/images/icons/* icons/
rm -rf src/technologies/*
cp wappalyzer/src/technologies/* src/technologies/

cp wappalyzer/src/categories.json src/categories.json

rm -rf wappalyzer