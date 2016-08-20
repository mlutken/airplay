#!/bin/bash
# Drupal restore script
 
if [ $USER == "sleipner" -o $USER == "root" ] ; then
	echo "*** WARNING WARNING WARNING ***"
	echo "You seem to be on the server or running as root"
	echo "Probably what you intend is NOT to restore the DB here."
	echo "In the rare cases that you actually do need to restore the DB on the server" 
	echo "please uncomment this test." 
	echo "JUST DON'T COMMIT THE CHANGES." 
#	exit
fi
 
 
if [ $# -ne 1 ]; then
  echo 1>&2 "Usage: $0 backup_file.mysql"
  exit 1
fi
 

USR="airplay_user"
PWD="Deeyl1819"
DB_MUSIC="airplay_music_v1"
HST="localhost"

echo "USR: $USR" 
echo "PWD: $PWD" 
echo "DB_MUSIC: $DB_MUSIC" 
echo "HST: $HST" 
 
 
# don't drop these tables
SKIP="Tables_in"
 
#------------------------------------------------------------------
 
# drop all tables, in case something got added since the last backup
for TBL in $(echo "show tables" | \
   mysql -h${HST} -u${USR} -p${PWD} ${DB_MUSIC} | grep -v -e ${SKIP})
do
  echo "drop table ${TBL}" | mysql -h${HST} -u${USR} -p${PWD} ${DB_MUSIC}
done
 
# now I can load the script
mysql --default-character-set=utf8 -h${HST} -u${USR} -p${PWD} ${DB_MUSIC} < $1
