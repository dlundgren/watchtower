<?php

require_once __DIR__ . '/../../vendor/autoload.php';

define('SUPPORT_FILE_PATH', realpath(__DIR__ . '/_files/'));

// default to UTC as I don't like warnings from unset datetime
date_default_timezone_set("UTC");
