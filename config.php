<?php

define("ROOT_DIR",dirname(__FILE__)."/");
define("DIR_VIEWS",ROOT_DIR."app/views/");
define("DIR_SERVICES",ROOT_DIR."app/services/");

define('DB_HOST','localhost');
define('DB_USER','homestead');
define('DB_PASS','secret');
define('DB_NAME','jira_tools');

define('JIRA_URL','url');
define('JIRA_USERNAME','user');
define('JIRA_PASSWORD','password');

define('INSTANCE_TIMEZONE','Europe/Lisbon');

require_once(DIR_VIEWS."common/utils.php");