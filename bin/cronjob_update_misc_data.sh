#!/bin/sh
TERM=linux
export TERM

. ${HOME}/.bash_aliases

drush ap_upd_misc_cron 

