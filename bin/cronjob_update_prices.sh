#!/bin/sh
TERM=linux
export TERM

. ${HOME}/.bashrc

echo "Before sleep"
sleep 2
echo "After sleep"
drush ap_upd_prices_cron "" 0 100000 

