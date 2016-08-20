#!/bin/sh
# Drupal restore script
 
if [ $# -ne 1 ]; then
  echo 1>&2 "Usage: $0 backup_file.mysql"
  exit 1
fi
 
SETTINGS_PHP="settings.php" 
SETTINGS_PHP=`find . -name ${SETTINGS_PHP}`
 
if [ ! -e $SETTINGS_PHP ]
then
   echo "Must run script in same directory as (or some parent of) settings.php"
   exit
fi
 
#------------------------------------------------------------------
 
# grab necessary values from settings.php
USR=`grep ^\\$db_url $SETTINGS_PHP | sed -n 's/.*\/\(.*\):.*/\1/p'`
PWD=`grep ^\\$db_url $SETTINGS_PHP | sed -n 's/.*:\(.*\)@.*/\1/p'`
DBN=`grep ^\\$db_url $SETTINGS_PHP | sed -n 's/.*\/\(.*\).;$/\1/p'`
HST=`grep ^\\$db_url $SETTINGS_PHP | sed -n 's/.*@\(.*\)\/.*/\1/p'`
 
echo "SETTINGS_PHP: $SETTINGS_PHP" 
echo "db_url: $db_url" 
echo "USR: $USR" 
echo "PWD: $PWD" 
echo "DBN: $DBN" 
echo "HST: $HST" 
echo "PRE: $PRE" 
 
 
# don't drop these tables
SKIP="Tables_in"
 
#------------------------------------------------------------------
 
# drop all tables, in case something got added since the last backup
for TBL in $(echo "show tables" | \
   mysql -h${HST} -u${USR} -p${PWD} ${DBN} | grep -v -e ${SKIP})
do
  echo "drop table ${TBL}" | mysql -h${HST} -u${USR} -p${PWD} ${DBN}
done
 
# now I can load the script
mysql --default-character-set=utf8 -h${HST} -u${USR} -p${PWD} ${DBN} < $1
