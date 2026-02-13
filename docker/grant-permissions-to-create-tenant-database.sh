#!/usr/bin/env bash
set -e

mysql --user=root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    GRANT ALL PRIVILEGES ON *.* TO '${MYSQL_USER}'@'%';
    FLUSH PRIVILEGES;
EOSQL
