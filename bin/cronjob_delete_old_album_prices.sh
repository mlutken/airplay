#!/bin/sh
TERM=linux
export TERM

. ${HOME}/.bashrc

drush ap_delete_old_album_prices_cron "" 0 100000 

