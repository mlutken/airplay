#!/bin/sh
TERM=linux
export TERM

. ${HOME}/.bashrc

drush cron 

