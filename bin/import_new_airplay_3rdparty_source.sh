#!/bin/sh 

# You must be in the unpacked source's root directory
# E.G:
# png-1.2.6 $ import_new_3rdparty_source.sh SOURCE_NAME VERSION_NUMBER CPAF_SUBDIR
# EXAMPLE :
# png-1.2.6 $ import_new_3rdparty_source.sh png 1.2.6 cul
 

if [ $# -lt 2 ]
# Test number of arguments to script (always a good idea).
then
    echo
    echo "You must be in the unpacked source's root directory"
    echo "NOTE: Currently the last copy to working dir is disabled, since for translations we don't want this."
    echo
    echo "Usage:   `basename $0` SOURCE_NAME VERSION_NUMBER SVN_SUBDIR (below veriquin/trunk)" 
    echo "Example: tidy-1.2.6 $ `basename $0` tidy 1.2.6 code/cpp/3rdparty"
    echo
    exit 0
fi


echo "Source Name                   : $1"
echo "Source Version                : $2"
echo "Subdirectory                  : $3"

svn import . https://angel1.projectlocker.com/nitram/airplay/svn/vendor/$1/current -m "Initial $1 vendor drop version $2"
svn copy https://angel1.projectlocker.com/nitram/airplay/svn/vendor/$1/current https://angel1.projectlocker.com/nitram/airplay/svn/vendor/$1/$2 -m "Tagging $1 version $2"  
#svn copy https://angel1.projectlocker.com/nitram/airplay/svn/vendor/$1/$2 https://angel1.projectlocker.com/nitram/airplay/svn/trunk/airplay/$3/$1 -m "Bringing $1 version $2 into main branch" 

echo
echo "svn import . https://angel1.projectlocker.com/nitram/airplay/svn/vendor/$1/current -m \"Initial $1 vendor drop version $2\""
echo 
echo "svn copy https://angel1.projectlocker.com/nitram/airplay/svn/vendor/$1/current https://angel1.projectlocker.com/nitram/airplay/svn/vendor/$1/$2 -m \"Tagging $1 version $2\""
echo
# echo "svn copy https://angel1.projectlocker.com/nitram/airplay/svn/vendor/$1/$2 https://angel1.projectlocker.com/nitram/airplay/svn/trunk/airplay/$3/$1 -m \"Bringing $1 version $2 into main branch\"" 
# echo



