#!/bin/bash
TERM=linux
export TERM

. ${HOME}/.bashrc

drush ap_read_upload_cron "${1}" 5000 "0" ${2}

