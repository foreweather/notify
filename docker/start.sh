#!/bin/bash
cp /www/application/config/env.php.bck /www/application/config/env.php

## Update PHP Environments
grep -l '###QUEUE_HOST###' /www/application/config/env.php | xargs sed -e 's,###QUEUE_HOST###,'${QUEUE_HOST}',g' -i
grep -l '###OAUTH_CLIENT_ID###' /www/application/config/env.php | xargs sed -e 's,###OAUTH_CLIENT_ID###,'${OAUTH_CLIENT_ID}',g' -i
grep -l '###OAUTH_CLIENT_SECRET###' /www/application/config/env.php | xargs sed -e 's,###OAUTH_CLIENT_SECRET###,'${OAUTH_CLIENT_SECRET}',g' -i
grep -l '###OAUTH_TOKEN_URL###' /www/application/config/env.php | xargs sed -e 's,###OAUTH_TOKEN_URL###,'${OAUTH_TOKEN_URL}',g' -i
grep -l '###OAUTH_CLIENT_SCOPE###' /www/application/config/env.php | xargs sed -e 's,###OAUTH_CLIENT_SCOPE###,'${OAUTH_CLIENT_SCOPE}',g' -i
grep -l '###API_BASE_URL###' /www/application/config/env.php | xargs sed -e 's,###API_BASE_URL###,'${API_BASE_URL}',g' -i
grep -l '###NOTIFY_HOUR###' /www/application/config/env.php | xargs sed -e 's,###NOTIFY_HOUR###,'${NOTIFY_HOUR}',g' -i
grep -l '###OAUTH_CLIENT_REDIRECT_URL###' /www/application/config/env.php | xargs sed -e 's,###OAUTH_CLIENT_REDIRECT_URL###,'${OAUTH_CLIENT_REDIRECT_URL}',g' -i
grep -l '###OAUTH_CLIENT_AUTHORIZE_URL###' /www/application/config/env.php | xargs sed -e 's,###OAUTH_CLIENT_AUTHORIZE_URL###,'${OAUTH_CLIENT_AUTHORIZE_URL}',g' -i
grep -l '###OAUTH_OWNER_DETAILS###' /www/application/config/env.php | xargs sed -e 's,###OAUTH_OWNER_DETAILS###,'${OAUTH_OWNER_DETAILS}',g' -i

########################################################################################################################
# Start fpm background
########################################################################################################################
php-fpm7 -D

########################################################################################################################
# Start ssh
########################################################################################################################
/usr/sbin/sshd -D &

cd /www && php notify.php
