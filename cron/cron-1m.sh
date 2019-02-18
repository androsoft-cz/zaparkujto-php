#!/bin/bash

APP=/srv
DEV=${APP}/dev
DEMO=${APP}/demo
STAGE=${APP}/stage
PRODUCTION=${APP}/production

# DEV
#php ${DEV}/www/index.php app:order:check
php ${DEV}/www/index.php app:reservation:cancelReserved
php ${DEV}/www/index.php app:email:notification

# DEMO
#php ${DEMO}/www/index.php app:order:check
php ${DEMO}/www/index.php app:reservation:cancelReserved
php ${DEMO}/www/index.php app:email:notification

# STAGE
#php ${STAGE}/www/index.php app:order:check
php ${STAGE}/www/index.php app:reservation:cancelReserved
php ${STAGE}/www/index.php app:email:notification

# PRODUCTION
#php ${PRODUCTION}/www/index.php app:order:check
php ${PRODUCTION}/www/index.php app:reservation:cancelReserved
php ${PRODUCTION}/www/index.php app:email:notification
