#!/bin/bash 

. ./env.webminer

#ROBOTS_SERVER_URL="http://filesupload.airplay.localhost"
ROBOTS_SERVER_URL="http://filesupload.airplaymusic.dk"
NUMBER_OF_ROBOTS=0
SUGGEST_USAGE="n"


# -------------------------------------
# --- Parse command line parameters ---
# -------------------------------------
for i in $*
do
	case $i in
    	-u=*|--url=*)
		ROBOTS_SERVER_URL=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
		;;
    	-n=*|--number-of-robots=*)
		NUMBER_OF_ROBOTS=`echo $i | sed 's/[-a-zA-Z0-9]*=//'`
    	;;
    	-h|--help)
		echo "Options:"
		echo "  -u=|--url=[$ROBOTS_SERVER_URL]" 
		echo "    Base url to server,path that handles robot job requests and status updates. For automining." 
		echo " " 
		echo "  -n=|--number-of-robots=[$NUMBER_OF_ROBOTS]" 
		echo "    Number of robots this server should create." 
		echo " " 
		echo "  -h=|--help" 
		echo "    Print this help" 
		echo " " 
		exit
		;;
    	--default)
		SUGGEST_USAGE="y"
		;;
    	*)
                # unknown option
		;;
  	esac
done

# ----------------------- 
# --- Print some info ---
# ----------------------- 
# echo "ROBOTS_SERVER_URL          : $ROBOTS_SERVER_URL"
# echo "NUMBER_OF_ROBOTS           : $NUMBER_OF_ROBOTS"
# # exit 0;

# -----------------------------
# --- Detect system bitwith ---
# -----------------------------
BITWIDTH=32
if [ -f "/lib64/libm.so.6" ]; then  BITWIDTH=64; fi
if [ -f "/usr/lib64/libz.so" ]; then  BITWIDTH=64; fi
if [ -d "/lib64/" ]; then  BITWIDTH=64; fi

WEBMINER_PHP_EXTENSIONS=""

# ----------------------------------------------------------
# --- XulRunner (hopefully old on it's way out version ) ---
# ----------------------------------------------------------
# XULRUNNNER_TAR=linux_${BITWIDTH}_release.tar.bz2
# MINER_BIN_DIR=${WEBMINER_ROOT_DIR}/webminer/linux/bin${BITWIDTH}
# XULRUNNER_BASE_DIR=${WEBMINER_ROOT_DIR}/webminer/linux/linux_${BITWIDTH}_release
# XULRUNNER_DIR=${XULRUNNER_BASE_DIR}/xulrunner
# 
# rm -rf ${XULRUNNER_BASE_DIR}
# pushd ${WEBMINER_ROOT_DIR}/webminer/linux
# tar xjvf ${XULRUNNNER_TAR}
# popd

# -------------------------------------
# --- Sleipner (new WebKit version) ---
# -------------------------------------
SLEIPNER_BASE_NAME=sleipner_linux_${BITWIDTH}
SLEIPNER_TAR=${SLEIPNER_BASE_NAME}.tar.bz2
SLEIPNER_BIN_DIR=${WEBMINER_ROOT_DIR}/webminer/bin


rm -rf ${SLEIPNER_BIN_DIR}
pushd ${WEBMINER_ROOT_DIR}/webminer
tar xjvf ../install/${SLEIPNER_TAR}
mv ${SLEIPNER_BASE_NAME} bin
popd



# ------------------------------------------
# --- Make a php.ini file for 'sleipner' ---
# ------------------------------------------
##sed -e "s#@WEBMINER_PHP_INCLUDE_PATH@#${WEBMINER_ROOT_DIR}/scripts:.:${SLEIPNER_BIN_DIR}:${SLEIPNER_BIN_DIR}/data/php/miner_utils:${SLEIPNER_BIN_DIR}/data/php/miner_templates#g" \
sed -e "s#@WEBMINER_PHP_INCLUDE_PATH@#${SLEIPNER_BIN_DIR}:${SLEIPNER_BIN_DIR}/data/php/miner_utils:${SLEIPNER_BIN_DIR}/data/php/miner_templates#g" \
-e "s#@WEBMINER_PHP_EXTENSION_DIR@#${SLEIPNER_BIN_DIR}#g" \
-e "s#@WEBMINER_PHP_EXTENSIONS@#${WEBMINER_PHP_EXTENSIONS}#g" \
-e "s#@WEBMINER_PHP_XULRUNNER_PATH@#${XULRUNNER_DIR}#g" webminer/bin/php.ini.in > webminer/bin/php.ini


# -------------------------------------------------------
# --- Make a minercreator.cfg file for 'minercreator' ---
# -------------------------------------------------------
sed -e "s#@PHP_INI_PATH@#${SLEIPNER_BIN_DIR}/php.ini#g" \
-e "s#@WEBMINER_PHP_XULRUNNER_PATH@#${XULRUNNER_DIR}#g" webminer/bin/minercreator.cfg.in > webminer/bin/minercreator.cfg

# ---------------------------------------------
# --- Make a crawler.cfg file for 'crawler' ---
# ---------------------------------------------
sed -e "s#@PHP_INI_PATH@#${SLEIPNER_BIN_DIR}/php.ini#g" \
-e "s#@WEBMINER_PHP_XULRUNNER_PATH@#${XULRUNNER_DIR}#g" webminer/bin/crawler.cfg.in > webminer/bin/crawler.cfg

# ----------------------------------------------
# --- Make a run_minercreator wrapper script ---
# ----------------------------------------------
sed -e "s#@SLEIPNER_BIN_DIR@#${SLEIPNER_BIN_DIR}#g" webminer/bin/run_minercreator.in > webminer/bin/run_minercreator
chmod a+x webminer/bin/run_minercreator

# ---------------------------------------------
# --- Make a run_crawler wrapper script ---
# ---------------------------------------------
sed -e "s#@SLEIPNER_BIN_DIR@#${SLEIPNER_BIN_DIR}#g" webminer/bin/run_crawler.in > webminer/bin/run_crawler
chmod a+x webminer/bin/run_crawler

# --------------------------------------
# --- Make a robot_settings.php file ---
# --------------------------------------
if [ $NUMBER_OF_ROBOTS != "0" ];
then
	echo "Create robot setup..."
	sed -e "s#@CRAWLER_BIN_DIR@#${SLEIPNER_BIN_DIR}#g" \
	-e "s#@SCRIPTS_BASE_DIR@#${WEBMINER_ROOT_DIR}#g" \
	-e "s#@ROBOTS_DIR@#${HOME}/robots#g" \
	-e "s#@NUMBER_OF_ROBOTS@#${NUMBER_OF_ROBOTS}#g" \
	-e "s#@ROBOTS_SERVER_URL@#${ROBOTS_SERVER_URL}#g" webminer/bin/robot/robot_settings.php.in > webminer/bin/robot/robot_settings.php
fi




# ---------------------------------------------------------
# --- Install PATH and WEBMINER_ROOT_DIR enviroment var ---
# ---------------------------------------------------------

# First get a copy of .bashrc with all webminer specific lines removed
cat ~/.bashrc | grep -v ADDED_BY_WEBMINER_INSTALLER > new.bashrc

# Add our PATH extention line
echo 'export WEBMINER_ROOT_DIR='"${WEBMINER_ROOT_DIR}" '# ADDED_BY_WEBMINER_INSTALLER' >> new.bashrc
echo 'export PATH=${WEBMINER_ROOT_DIR}/webminer/bin:${WEBMINER_ROOT_DIR}/webminer/linux:${PATH} # ADDED_BY_WEBMINER_INSTALLER' >> new.bashrc

# overwrite original .bashrc with our new one and delete the new.bashrc
cp -f ./new.bashrc ~/.bashrc
rm ./new.bashrc

echo " "
echo "ROBOTS_SERVER_URL          : $ROBOTS_SERVER_URL"
echo "NUMBER_OF_ROBOTS           : $NUMBER_OF_ROBOTS"
echo " "
echo "Please run 'source env.webminer' or open a new prompt before running scripts"
echo " "
echo "New minercreator can be found in webminer/bin/minercreator and you can run it using:"
echo "'run_minercreator'"
echo " "
echo "New crawler can be found in webminer/bin/crawler and you can run it using:"
echo "'run_crawler'"
echo " "
echo "For command line help add --help after the run_crawler or run_testcreator commands."
