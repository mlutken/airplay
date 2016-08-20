#!/bin/sh
TERM=linux
export TERM

. ${HOME}/.bash_aliases

#drush xmlsitemap-rebuild
#drush ap_upd_sitemap_cron update dk
#drush ap_upd_sitemap_cron update uk

a7drush ap_upd_sitemap_cron update dk
a7drush ap_upd_sitemap_cron update uk
