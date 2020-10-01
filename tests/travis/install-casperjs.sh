#!/bin/sh
set -e

# check to see if casperjs folder is empty
if [ ! -d "$HOME/casperjs-1.1.4/bin" ]; then
  wget https://github.com/n1k0/casperjs/archive/1.1.4.tar.gz -O $HOME/casper.tar.gz;
  tar -xvf $HOME/casper.tar.gz -C $HOME;
  echo 'casperjs installed.'
else
  echo 'Using cached directory for casperjs.';
fi
