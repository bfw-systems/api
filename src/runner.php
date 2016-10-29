<?php

$bfwApi = new \BfwApi\BfwApi($module);

$app = \BFW\Application::getInstance();
$app->attach($bfwApi);
