<?php

$bfwApi = new \BfwApi\BfwApi($this);

$app = \BFW\Application::getInstance();
$app->attach($bfwApi);

\BFW\Helpers\Constants::create('API_DIR', SRC_DIR.'api/');
$app->getComposerLoader()->addPsr4('Api\\', API_DIR);
