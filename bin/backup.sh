#!/bin/bash
# Drupal/Music/Code backup script
date

if [ "$USER" = "" ];
then
	USER="sleipner"
fi


BACKUP_BASE_NAME="LATEST"
BACKUP_BASE_DIR="/home/$USER/BACKUP_AIRPLAY"
#BACKUP_BASE_DIR="/home/sleipner/BACKUP_AIRPLAY"
BACKUP_DB="n"
BACKUP_MUSIC_DB="n"
BACKUP_MUSIC_STRUCTURE="n"
BACKUP_DRUPAL_DB="n"
BACKUP_CODE="n"
DELETE_BACKUP_DIR_AFTER="y"
COPY_TO_LATEST="n"

USR="airplay_user"
PWD="Deeyl1819"
DB_MUSIC="airplay_music_v1"
DB_DRUPAL="airplay_drupal7"
HST="localhost"

# -------------------------------------
# --- Parse command line parameters ---
# -------------------------------------
for i in $*
do
	case $i in
    	--base-name=*)
		BACKUP_BASE_NAME=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
		;;
    	--base-dir=*)
		BACKUP_BASE_DIR=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
		;;
    	-db=*|--do-backup-db=*)
		BACKUP_DB=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
		;;
    	-dbm=*|--backup-music-db=*)
		BACKUP_MUSIC_DB=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
		;;
    	-dbs=*|--backup-db-structure=*)
		BACKUP_MUSIC_STRUCTURE=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
		;;
    	-dp=*|--do-backup-drupal=*)
		BACKUP_DRUPAL_DB=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
		;;
    	-code=*|--do-backup-code=*)
		BACKUP_CODE=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
    	;;
    	-latest=*|--do-copy-latest=*)
		COPY_TO_LATEST=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
    	;;
    	--do-delete-dir=*)
		DELETE_BACKUP_DIR_AFTER=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
    	;;
    	-h|--help)
		echo "Options:"
		echo "  --base-name=[LATEST]" 
		echo "    Base name of backup file(s)" 
		echo " " 
		echo "  --base-dir=[$BACKUP_BASE_DIR]" 
		echo "    Base directory for file(s)" 
		echo " " 
		echo "  -db|--do-backup-db=[$BACKUP_DB]" 
		echo "    Do perform backup of database (Drupal and Music)" 
		echo " " 
		echo "  -dbm|--backup-music-db=[$BACKUP_MUSIC_DB]" 
		echo "    Do perform backup of music database (full)" 
		echo " " 
		echo "  -dbs|--backup-db-structure=[$BACKUP_MUSIC_STRUCTURE]" 
		echo "    Do perform backup of music DB(Structure only + few essential data)" 
		echo " " 
		echo "  -dp|--do-backup-drupal=[$BACKUP_DRUPAL_DB]" 
		echo "    Do perform backup of Druapl database only" 
		echo " " 
		echo "  -code|--do-backup-code=[$BACKUP_CODE]" 
		echo "    Do perform backup of code" 
		echo " " 
		echo "  -latest|--do-copy-latest=[$COPY_TO_LATEST]" 
		echo "    Create a 'latest' copy of the tarball named: 'airplay_latest.BAK.tar.bz2'" 
		echo " " 
		echo "  --do-delete-dir=[$DELETE_BACKUP_DIR_AFTER]" 
		echo "    Do delete backup directory after backup leaving only the (compressed) tarball" 
		echo " " 
		echo "*** Reserved parameters for option '--base-name=RESERVED-PARAM-NAME'  ***" 
		echo " " 
		echo "    DAY-OF-YEAR   : Use curent day of year (001..366) as basename"
		echo "    WEEK-OF-YEAR  : Use curent week of year (01..53) as basename"
		echo "    DAY-OF-WEEK   : Use curent day of week (1..7) as basename"
		echo "    DAY-OF-MONTH  : Use curent day of month (01..31) as basename"
		echo "    DATE          : Use curent date (YYYY-MM-DD) as basename"
		echo "    DATE-TIME     : Use curent date and time (YYYY-MM-DD-HHMM) as basename"
		echo " " 
		exit
		;;
    	--default)
		DEFAULT=YES
		;;
    	*)
                # unknown option
		;;
  	esac
done


# -------------------------------------------------------------------
# --- Parsing of special/reserved words options for '--base-name' ---
# -------------------------------------------------------------------
case $BACKUP_BASE_NAME in
	DAY-OF-YEAR)
	echo "DAY-OF-YEAR ......"
	BACKUP_BASE_NAME=`date +day-of-year_%j`
	;;
	WEEK-OF-YEAR)
	echo "WEEK-OF-YEAR ......"
	BACKUP_BASE_NAME=`date +week-of-year_%V`
	;;
	DAY-OF-WEEK)
	echo "DAY-OF-WEEK ......"
	BACKUP_BASE_NAME=`date +day-of-week_%u`
	;;
	DAY-OF-MONTH)
	echo "DAY-OF-MONTH ......"
	BACKUP_BASE_NAME=`date +day-of-month_%d`
	;;
	DATE)
	echo "DATE ......"
	BACKUP_BASE_NAME=`date +date_%Y-%m-%d`
	;;
	DATE-TIME)
	echo "DATE-TIME ......"
	BACKUP_BASE_NAME=`date +date-time_%Y-%m-%d-%H%M`
	;;
	--default)
	DEFAULT=YES
	;;
	*)
			# unknown option
	;;
esac


# -----------------------------------------------------
# --- Check that we are running from this directory ---
# -----------------------------------------------------
if [ ! -e "backup.sh" ]
then
   echo "Must run script from scripts own (bin) directory"
   exit
fi

 
BACKUP_DIR="$BACKUP_BASE_DIR/$BACKUP_BASE_NAME"


MUSIC_FULL_SQL_FILE="${BACKUP_DIR}/backup-music.mysql"
MUSIC_STRUCTURE_SQL_FILE="${BACKUP_DIR}/backup-music-structure.mysql"
DRUPAL_SQL_FILE="${BACKUP_DIR}/backup-drupal.mysql"


# ----------------------- 
# --- Print some info ---
# ----------------------- 
echo "BACKUP_BASE_NAME              : $BACKUP_BASE_NAME"
echo "BACKUP_BASE_DIR               : $BACKUP_BASE_DIR"
echo "BACKUP_DIR                    : $BACKUP_DIR"
echo "BACKUP_DB                     : $BACKUP_DB"
echo "BACKUP_MUSIC_DB               : $BACKUP_MUSIC_DB"
echo "BACKUP_MUSIC_STRUCTURE        : $BACKUP_MUSIC_STRUCTURE"
echo "BACKUP_DRUPAL_DB              : $BACKUP_DRUPAL_DB"
echo "BACKUP_CODE                   : $BACKUP_CODE"
echo "DELETE_BACKUP_DIR_AFTER       : $DELETE_BACKUP_DIR_AFTER"

#exit

# --------------------
# --- Run commands ---
# --------------------

# --- Remove and create backup DB directory ---
echo "Removing and creating backup dir : $BACKUP_DIR ..."
if [ -e $BACKUP_DIR ]
then
	rm -rf  $BACKUP_DIR
fi
mkdir -p $BACKUP_DIR


# --- Backup DB (Music and Drupal) ---
if [ $BACKUP_DB == "y" ];
then
	echo "Making DB backup: backup-music_${BACKUP_BASE_NAME}.mysql AND backup-drupal_${BACKUP_BASE_NAME}.mysql"
	rm -f $MUSIC_FULL_SQL_FILE
	rm -f $DRUPAL_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_MUSIC} > $MUSIC_FULL_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_DRUPAL} > $DRUPAL_SQL_FILE
fi

# --- Backup Music DB (full) ---
if [ $BACKUP_MUSIC_DB == "y" ];
then
	rm -f $MUSIC_FULL_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_MUSIC} > $MUSIC_FULL_SQL_FILE
fi

# --- Backup Music DB (structure and a few essential data ) ---
if [ $BACKUP_MUSIC_STRUCTURE == "y" ];
then
	rm -f $MUSIC_STRUCTURE_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --no-data --add-drop-table ${DB_MUSIC} > $MUSIC_STRUCTURE_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_MUSIC} artist_various >> $MUSIC_STRUCTURE_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_MUSIC} country >> $MUSIC_STRUCTURE_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_MUSIC} currency >> $MUSIC_STRUCTURE_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_MUSIC} currency_to_euro >> $MUSIC_STRUCTURE_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_MUSIC} genre >> $MUSIC_STRUCTURE_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_MUSIC} job >> $MUSIC_STRUCTURE_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_MUSIC} media_format >> $MUSIC_STRUCTURE_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_MUSIC} quiz >> $MUSIC_STRUCTURE_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_MUSIC} record_store >> $MUSIC_STRUCTURE_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_MUSIC} record_store_media_format_rel >> $MUSIC_STRUCTURE_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_MUSIC} record_store_review_settings >> $MUSIC_STRUCTURE_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_MUSIC} item_price_delivery_status >> $MUSIC_STRUCTURE_SQL_FILE
	
fi


# --- Backup Drupal DB (full) ---
if [ $BACKUP_DRUPAL_DB == "y" ];
then
	rm -f $DRUPAL_SQL_FILE
	mysqldump -h${HST} -u${USR} -p${PWD} --add-drop-table ${DB_DRUPAL} > $DRUPAL_SQL_FILE
fi



# --- Backup code (top directory 'airplay') ---
if [ $BACKUP_CODE == "y" ];
then
	echo "Making CODE backup: "
	pushd "../.."
	tar cf ${BACKUP_DIR}/airplay.code.tar ./airplay
	popd
fi

# --- Create 'topleveldirname.txt' file with the name of the toplevel directory in the tarball (${BACKUP_BASE_NAME})
pushd ${BACKUP_DIR}
echo ${BACKUP_BASE_NAME} > topleveldirname.txt
popd

# --- Create compressed tarball and copy as latest if requested ---
echo "Creating compressed tarball of backed up data: "
pushd ${BACKUP_BASE_DIR}
tar cjvf ${BACKUP_BASE_DIR}/airplay_${BACKUP_BASE_NAME}.BAK.tar.bz2 ${BACKUP_BASE_NAME}

if [ $COPY_TO_LATEST == "y" ];
then
	echo "Copying backup to : 'airplay_latest.BAK.tar.bz2' "
	cp airplay_${BACKUP_BASE_NAME}.BAK.tar.bz2 airplay_latest.BAK.tar.bz2
fi
popd


# --- Delete backup temporary backup directory if requested ---
if [ $DELETE_BACKUP_DIR_AFTER == "y" ];
then
	echo "Deleting backup dir: ${BACKUP_DIR}"
	rm -rf  $BACKUP_DIR
fi


