#!/bin/bash
# Drupal/Music restore script

DEV_MACHINE_RESTORE="y"

#if [ 1 ] ; then
if [ $USER == "sleipner" -o $USER == "root" ] 
then
    DEV_MACHINE_RESTORE="n"
	echo "*** WARNING WARNING WARNING ***"
	echo "You seem to be on the server or running as root"
	echo "Probably what you intend is NOT to restore the DB here."
	echo "In the rare cases that you actually do need to restore the DB on the server" 
	echo "please comment in the if [ 1 ] test." 
	echo "JUST DON'T COMMIT THE CHANGES." 
#	exit
fi

FILE_NAME=""
RESTORE_DRUPAL_DB="n"
RESTORE_MUSIC_DB="n"
RESTORE_MUSIC_STRUCTURE_DB="n"
DELETE_TEMP_DIR_AFTER_RESTORE="n"

USR="airplay_user"
DB_PASS="Deeyl1819"
HST="localhost"



# -------------------------------------
# --- Parse command line parameters ---
# -------------------------------------
for i in $*
do
	case $i in
    	-f=*|--file=*)
		FILE_NAME=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
		;;
    	-dp=*|--restore-drupal-db=*)
		RESTORE_DRUPAL_DB=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
		;;
    	-dbs=*|--restore-structure-db=*)
		RESTORE_MUSIC_STRUCTURE_DB=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
		;;
    	-dbm=*|--restore-music-db=*)
		RESTORE_MUSIC_DB=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
		;;
    	-d=*|--do-delete-temp=*)
		DELETE_TEMP_DIR_AFTER_RESTORE=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
    	;;
    	-h|--help)
		echo "Options:"
		echo "  -f=*|--file=[$FILE_NAME]" 
		echo "    Path to backup file to use when restoring" 
		echo " " 
		echo "  --restore-drupal-db=[$RESTORE_DRUPAL_DB]" 
		echo "    Do restore Drupal database" 
		echo " " 
		echo "  --restore-music-db=[$RESTORE_MUSIC_DB]" 
		echo "    Do restore Music database" 
		echo " " 
		echo "  --restore-structure-db=[$RESTORE_MUSIC_STRUCTURE_DB]" 
		echo "    Do restore Music database (structure and essential data only)" 
		echo " " 
		echo "  --do-delete-temp=[$DELETE_TEMP_DIR_AFTER_RESTORE]" 
		echo "    Do delete restore directory after restore" 
		echo " " 
		echo "*** Reserved parameters for option '--file=RESERVED-PARAM-NAME'  ***" 
		echo " " 
        echo "    GET-LATEST            : Get latest backup from server"
        echo "    GET-LATEST-DRUPAL 	: Get latest Drupal backup from server"
        echo "    GET-LATEST-STRUCTURE  : Get music structure and essential data backup from server"
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


# -----------------------------------------------------
# --- Check that we are running from this directory ---
# -----------------------------------------------------
if [ ! -e "restore.sh" ]
then
   echo "Must run script from scripts own (bin) directory"
   exit
fi


 
RESTORE_DIR="./AIRPLAY_RESTORE_DIR"
if [ ! -e $RESTORE_DIR ]
then
	mkdir -p $RESTORE_DIR
fi

RESTORE_DIR=`cd ${RESTORE_DIR} && pwd`

if [ $FILE_NAME == "GET-LATEST-DRUPAL" ];
then
    RESTORE_MUSIC_DB="n"
fi

# ----------------------- 
# --- Print some info ---
# ----------------------- 
echo "RESTORE_DIR                    : $RESTORE_DIR"
echo "FILE_NAME                      : $FILE_NAME"
echo "RESTORE_DRUPAL_DB              : $RESTORE_DRUPAL_DB"
echo "RESTORE_MUSIC_DB               : $RESTORE_MUSIC_DB"
echo "DELETE_TEMP_DIR_AFTER_RESTORE  : $DELETE_TEMP_DIR_AFTER_RESTORE"

# exit

# --------------------
# --- Run commands ---
# --------------------

# --- Remove and create restore DB directory ---
echo "Removing and creating restore dir : $RESTORE_DIR ..."
if [ -e $RESTORE_DIR ]
then
	rm -rf  $RESTORE_DIR
fi
mkdir -p $RESTORE_DIR


# -------------------------------------------------------------
# --- Special treatment of 'GET-LATEST' option for filename ---
# -------------------------------------------------------------
if [ $FILE_NAME == "GET-LATEST" ];
then
	echo "Getting latest backup from server ('www.airplaymusic.dk:/home/sleipner/BACKUP_AIRPLAY/airplay_latest.BAK.tar.bz2') ..."
 	scp sleipner@www.airplaymusic.dk:/home/sleipner/BACKUP_AIRPLAY/airplay_latest.BAK.tar.bz2 $RESTORE_DIR
	FILE_NAME=${RESTORE_DIR}/airplay_latest.BAK.tar.bz2
	echo "FILE_NAME (GET-LATEST)        : $FILE_NAME"
fi

if [ $FILE_NAME == "GET-LATEST-DRUPAL" ];
then
    echo "Getting latest Drupal backup from server ('www.airplaymusic.dk:/home/sleipner/BACKUP_AIRPLAY/airplay_LATEST-DRUPAL.BAK.tar.bz2') ..."
    scp sleipner@www.airplaymusic.dk:/home/sleipner/BACKUP_AIRPLAY/airplay_LATEST-DRUPAL.BAK.tar.bz2 $RESTORE_DIR
    FILE_NAME=${RESTORE_DIR}/airplay_LATEST-DRUPAL.BAK.tar.bz2
    echo "FILE_NAME (GET-LATEST-DRUPAL)       : $FILE_NAME"
fi

if [ $FILE_NAME == "GET-LATEST-STRUCTURE" ];
then
    echo "Getting latest Music DB structure backup from server ('www.airplaymusic.dk:/home/sleipner/BACKUP_AIRPLAY/airplay_LATEST-MUSIC-STRUCTURE.BAK.tar.bz2') ..."
    scp sleipner@www.airplaymusic.dk:/home/sleipner/BACKUP_AIRPLAY/airplay_LATEST-MUSIC-STRUCTURE.BAK.tar.bz2 $RESTORE_DIR
    FILE_NAME=${RESTORE_DIR}/airplay_LATEST-MUSIC-STRUCTURE.BAK.tar.bz2
    echo "FILE_NAME (LATEST-MUSIC-STRUCTURE)       : $FILE_NAME"
fi


# --- Unpack tarball ---
pushd $RESTORE_DIR
tar xjvf $FILE_NAME
# --- Get toplevel dir name in tarball ---
TOPLEVELDIRNAME_TXT_PATH=`find . -name topleveldirname.txt`
TOPLEVELDIRNAME=`cat ${TOPLEVELDIRNAME_TXT_PATH}`
TOPLEVELDIRNAME=`cd ${TOPLEVELDIRNAME} && pwd`
popd


# ----------------------------------
# --- Create DB restore commands --- 
# ----------------------------------
CMD_DRUPAL="./restore_drupal_db.sh ${TOPLEVELDIRNAME}/backup-drupal.mysql"
CMD_MUSIC="./restore_music_db.sh ${TOPLEVELDIRNAME}/backup-music.mysql"
CMD_MUSIC_STRUCTURE="./restore_music_db.sh ${TOPLEVELDIRNAME}/backup-music-structure.mysql"


echo "TOPLEVELDIRNAME_TXT_PATH       : $TOPLEVELDIRNAME_TXT_PATH"
echo "TOPLEVELDIRNAME                : $TOPLEVELDIRNAME"
echo "CMD_MUSIC                      : $CMD_MUSIC"
echo "CMD_DRUPAL                     : $CMD_DRUPAL"


# --- Restore Drupal ---
if [ $RESTORE_DRUPAL_DB == "y" ];
then
    DBN="airplay_drupal7"
	echo "Restoring Drupal database ..."
 	sh $CMD_DRUPAL

 	# if on localhost fix the language table in drupal so we dont redirect to live site from localhost
    if [ $DEV_MACHINE_RESTORE == "y" ];
    then
        echo "Fixing restoring DEV_MACHINE .localhost languages/domain redirect to LIVE problem"
        mysql --default-character-set=utf8 -h${HST} -u${USR} -p${DB_PASS} ${DBN} < restore_languages_localhost.sql
    fi
fi

# --- Restore Music ---
if [ $RESTORE_MUSIC_DB == "y" ];
then
    DBN="airplay_music_v1"
	echo "Restoring Music database ..."
 	sh $CMD_MUSIC
fi

# --- Restore Music (structure only) ---
if [ $RESTORE_MUSIC_STRUCTURE_DB == "y" ];
then
    DBN="airplay_music_v1"
	echo "Restoring Music database ..."
 	sh $CMD_MUSIC_STRUCTURE
fi

if [ $DELETE_TEMP_DIR_AFTER_RESTORE == "y" ];
then
	echo "Deleting restore dir: ${RESTORE_DIR}"
 	rm -rf  $RESTORE_DIR
fi

