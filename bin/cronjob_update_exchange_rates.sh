#!/bin/sh
TERM=linux
export TERM

. ${HOME}/.bashrc

drush ap_upd_rates_cron 

