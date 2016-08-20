#!/bin/sh
# Drupal backup script
 
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
 
# tables with transient data that should not be backed up
SKIP="^access$\|access_log\|cache\|search_index\|sessions\|^statistics$\|watchdog\|Tables_in"
 
# grab necessary values from settings.php
USR=`grep ^\\$db_url $SETTINGS_PHP | sed -n 's/.*\/\(.*\):.*/\1/p'`
PWD=`grep ^\\$db_url $SETTINGS_PHP | sed -n 's/.*:\(.*\)@.*/\1/p'`
DBN=`grep ^\\$db_url $SETTINGS_PHP | sed -n 's/.*\/\(.*\).;$/\1/p'`
HST=`grep ^\\$db_url $SETTINGS_PHP | sed -n 's/.*@\(.*\)\/.*/\1/p'`
PRE=`grep ^\\$db_prefix $SETTINGS_PHP | sed -n "s/.*'\(.*\)';/\1/p"`
 
#------------------------------------------------------------------
 
echo "SETTINGS_PHP: $SETTINGS_PHP" 
echo "db_url: $db_url" 
echo "USR: $USR" 
echo "PWD: $PWD" 
echo "DBN: $DBN" 
echo "HST: $HST" 
echo "PRE: $PRE" 
 
# remove any existing data
rm -f $1
 
# dump out the structure of all tables
mysqldump -h${HST} -u${USR} -p${PWD} \
   -d -e -q --compact --single-transaction \
   --add-drop-table ${DBN} > $1
 
# dump the data, skipping tables indicated in skip list
for TBL in $(echo "show tables" | \
   mysql -h${HST} -u${USR} -p${PWD} ${DBN} | grep -v -e ${SKIP})
do
mysqldump -h${HST} -u${USR} -p${PWD} \
   -e -q -t --compact --skip-extended-insert \
   --single-transaction --add-drop-table ${DBN} ${TBL} >> $1
done
 
# MySQL doesn't like zeros in autoincrement columns and will screw
# up the anonymous user record
echo "UPDATE \`$PRE""users\` SET uid=0 WHERE name='';" >> $1
