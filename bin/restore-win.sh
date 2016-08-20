#!/bin/sh
# Drupal/Music restore script

if [ "${USER}" == "sleipner" -o "${USER}" == "root" ] ; then
	echo "*** WARNING WARNING WARNING ***"
	echo "You seem to be on the server or running as root"
	echo "Probably what you intend is NOT to restore the DB here."
	echo "In the rare cases that you actually do need to restore the DB on the server" 
	echo "please uncomment this test." 
	echo "JUST DON'T COMMIT THE CHANGES." 
	exit
fi

FILE_NAME=""
RESTORE_DRUPAL_DB="y"
RESTORE_MUSIC_DB="y"
DELETE_TEMP_DIR_AFTER_RESTORE="n"


# -------------------------------------
# --- Parse command line parameters ---
# -------------------------------------
for i in $*
do
	case $i in
    	-f=*|--file=*)
		FILE_NAME=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
		;;
    	--restore-drupal-db=*)
		RESTORE_DRUPAL_DB=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
		;;
    	--restore-music-db=*)
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
		echo "  --do-delete-temp=[$DELETE_TEMP_DIR_AFTER_RESTORE]" 
		echo "    Do delete restore directory after restore" 
		echo " " 
		echo "*** Reserved parameters for option '--file=RESERVED-PARAM-NAME'  ***" 
		echo " " 
		echo "    GET-LATEST   : Get latest backup from server"
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

RESTORE_DIR_ABS=`cd ${RESTORE_DIR} && pwd`



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
	echo "Getting latest backup from server ('airplay.ath.cx:/home/sleipner/BACKUP_AIRPLAY/airplay_latest.BAK.tar.bz2') ..."
 	scp sleipner@airplay.ath.cx:/home/sleipner/BACKUP_AIRPLAY/airplay_latest.BAK.tar.bz2 $RESTORE_DIR
	FILE_NAME=${RESTORE_DIR_ABS}/airplay_latest.BAK.tar.bz2
	echo "FILE_NAME (GET-LATEST)        : $FILE_NAME"
fi

# --- Get tar filename (i.e. without the .bz2 extension)  ---
FILE_NAME_TAR=`echo $FILE_NAME | sed 's/\.bz2//'`
FILE_NAME=`basename $FILE_NAME`
FILE_NAME_TAR=`basename $FILE_NAME_TAR`


# ----------------------- 
# --- Print some info ---
# ----------------------- 
echo "RESTORE_DIR                    : $RESTORE_DIR"
echo "FILE_NAME                      : $FILE_NAME"
echo "FILE_NAME_TAR                  : $FILE_NAME_TAR"
echo "RESTORE_DRUPAL_DB              : $RESTORE_DRUPAL_DB"
echo "RESTORE_MUSIC_DB               : $RESTORE_MUSIC_DB"
echo "DELETE_TEMP_DIR_AFTER_RESTORE  : $DELETE_TEMP_DIR_AFTER_RESTORE"

# --- Unpack tarball ---
pushd $RESTORE_DIR
pwd
7z x -y $FILE_NAME
7z x -y $FILE_NAME_TAR
# --- Get toplevel dir name in tarball ---
TOPLEVELDIRNAME_TXT_PATH=`find . -name topleveldirname.txt`
TOPLEVELDIRNAME=`cat ${TOPLEVELDIRNAME_TXT_PATH}`
TOPLEVELDIRNAME=`cd ${TOPLEVELDIRNAME} && pwd`

echo "TOPLEVELDIRNAME  : $TOPLEVELDIRNAME"
popd


# ----------------------------------
# --- Create DB restore commands --- 
# ----------------------------------
CMD_MUSIC="restore_music_db-win.sh ${TOPLEVELDIRNAME}/backup-music.mysql"
CMD_DRUPAL="restore_drupal_db-win.sh ${TOPLEVELDIRNAME}/backup-drupal.mysql"


echo "TOPLEVELDIRNAME_TXT_PATH       : $TOPLEVELDIRNAME_TXT_PATH"
echo "TOPLEVELDIRNAME                : $TOPLEVELDIRNAME"
echo "CMD_MUSIC                      : $CMD_MUSIC"
echo "CMD_DRUPAL                     : $CMD_DRUPAL"

# --- Restore Music ---
if [ $RESTORE_MUSIC_DB == "y" ];
then
	echo "Restoring Music database ..."
 	sh $CMD_MUSIC
fi

# --- Restore Drupal ---
if [ $RESTORE_DRUPAL_DB == "y" ];
then
	echo "Restoring Drupal database ..."
 	sh $CMD_DRUPAL
fi


if [ $DELETE_TEMP_DIR_AFTER_RESTORE == "y" ];
then
	echo "Deleting restore dir: ${RESTORE_DIR}"
 	rm -rf  $RESTORE_DIR
fi


# C:\code\crawler\code\cpp\3rdparty\php\windows\bin;C:\code\crawler\code\cpp\bin;C:\code\crawler\code\bin;C:\code\crawler\code\bin\windows;C:\code\cpaf\cbs;C:\code\miners\webminer\windows;C:\code\cpaf\cbs;C:\Perl\site\bin;C:\Perl\bin;C:\wamp\bin\php\php5.2.6;C:\wamp\bin\php\php5.2.6;C:\wamp\bin\php\php5.2.6;C:\wamp\bin\php\php5.2.6;C:\wamp\bin\php\php5.2.6;C:\wamp\bin\php\php5.2.6;C:\wamp\bin\php\php5.2.6;C:\wamp\bin\php\php5.2.6;C:\code\miners\webminer\windows;C:\Programmer\CollabNet Subversion;C:\code\miners\webminer\windows;C:\code\miners\webminer\windows;C:\code\veriquin\code\cpp\3rdparty\php\windows\bin;C:\code\veriquin\code\cpp\bin;C:\code\veriquin\code\bin;C:\code\veriquin\code\bin\windows;C:\code\cpaf\cbs;C:\Programmer\CollabNet Subversion;C:\Programmer\TortoiseSVN\bin;C:\UnxUtils\usr\local\wbin;C:\Programmer\copSSH\bin;C:\Programmer\CMake 2.6\bin;C:\wamp\bin\mysql\mysql5.0.51b\bin;C:\Programmer\7-Zip;C:\WINDOWS\system32;C:\WINDOWS;C:\WINDOWS\System32\Wbem


