#!/bin/bash
mysql -uroot -p$MYSQL_ROOT_PASSWORD -e "SET GLOBAL log_bin_trust_function_creators = 1; SET PERSIST log_bin_trust_function_creators = 1;"