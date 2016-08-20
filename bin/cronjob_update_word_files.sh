#!/bin/sh
TERM=linux
export TERM

. ${HOME}/.bash_aliases

a7drush ap_upd_wordfiles_cron
