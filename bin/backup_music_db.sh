#!/bin/sh
# Drupal backup script
 
if [ $# -ne 1 ]; then
  echo 1>&2 "Usage: $0 backup_file.mysql"
  exit 1
fi
 
SETTINGS_PHP="settings.php" 
SETTINGS_PHP=`find ../drupal -name ${SETTINGS_PHP}`
 
if [ ! -e $SETTINGS_PHP ]
then
   echo "Must run script in same directory as (or some parent of) settings.php"
   exit
fi
 
#------------------------------------------------------------------
 
USR="airplay_user"
PWD="Deeyl1819"
DBN="airplay_music_v1"
HST="localhost"
 
#------------------------------------------------------------------
 
echo "USR: $USR" 
echo "PWD: $PWD" 
echo "DBN: $DBN" 
echo "HST: $HST" 
echo "PRE: $PRE" 
 
# remove any existing data
rm -f $1

# dump out the structure of all tables
mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DBN} > $1
 
