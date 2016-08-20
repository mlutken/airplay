#!/bin/sh 

CWD=`pwd`

drush ap_read_excel_data ${CWD}/Pop-rock.csv "Pop/Rock"
drush ap_read_excel_data ${CWD}/Country-Folk#-Z.csv "Country/Folk"
drush ap_read_excel_data ${CWD}/Heavy-Metal#-Z.csv "Metal/Hard Rock"
drush ap_read_excel_data ${CWD}/HipHop-Rap#-Z.csv "HipHop/Rap"
drush ap_read_excel_data ${CWD}/Jazz-Blues.csv "Jazz/Blues"
drush ap_read_excel_data ${CWD}/RnB-Soul#-Z.csv "Soul/R&B"
drush ap_read_excel_data ${CWD}/Techno-Dance#-Z.csv "Dance/Electronic"

# Pop-rock.csv
# Country-Folk#-Z.csv
# Heavy-Metal#-Z.csv
# HipHop-Rap#-Z.csv
# Jazz-Blues.csv
# RnB-Soul#-Z.csv
# Techno-Dance#-Z.csv

# Pop/Rock
# Soul/R&B
# Dance/Electronic
# HipHop/Rap
# Metal/Hard Rock
# Country/Folk
# Jazz/Blues
# Classical
# Entertainment
# Kids
# Other
# New age
# World/Reggae
# Soundtrack
