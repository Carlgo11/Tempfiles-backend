<?php

namespace com\carlgo11\tempfiles\api;
require_once __DIR__ . '/../../src/com/carlgo11/tempfiles/autoload.php';
require_once __DIR__ . '/../../src/com/carlgo11/tempfiles/api/Cleanup.php';

new Cleanup($_SERVER['REQUEST_METHOD']);
