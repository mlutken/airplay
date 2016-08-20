#!/bin/sh
# Drupal backup script
 
if [ $# -ne 1 ]; then
  echo 1>&2 "Usage: $0 backup_file.mysql"
  exit 1
fi
 

USR="airplay_user"
PWD="Deeyl1819"
DBN="airplay_drupal7"
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


# -------------------------
# --- OLD dump commands ---
# -------------------------

# # tables with transient data that should not be backed up
# #SKIP="^access$\|access_log\|cache\|search_index\|sessions\|^statistics$\|watchdog\|Tables_in"
# SKIP="SKIP_NOTHING_XXX"
# 
# # dump out the structure of all tables
# mysqldump -h${HST} -u${USR} -p${PWD} \
#    -d -e -q --compact --single-transaction \
#    --add-drop-table ${DBN} > $1
#  
# # dump the data, skipping tables indicated in skip list
# for TBL in $(echo "show tables" | \
#    mysql -h${HST} -u${USR} -p${PWD} ${DBN} | grep -v -e ${SKIP})
# do
# mysqldump -h${HST} -u${USR} -p${PWD} \
#    -e -q -t --compact --skip-extended-insert \
#    --single-transaction --add-drop-table ${DBN} ${TBL} >> $1
# done
#  
# # MySQL doesn't like zeros in autoincrement columns and will screw
# # up the anonymous user record
# echo "UPDATE \`$PRE""users\` SET uid=0 WHERE name='';" >> $1
