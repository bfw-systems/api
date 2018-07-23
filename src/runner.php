<?php

$this->monolog = new \BFW\Monolog(
    'bfw-api',
    \BFW\Application::getInstance()->getConfig()
);
$this->monolog->addAllHandlers();

$bfwApi = new \BfwApi\BfwApi($this);

$app        = \BFW\Application::getInstance();
$appSubject = $app->getSubjectList()->getSubjectForName('ApplicationTasks');
$appSubject->attach($bfwApi);

\BFW\Helpers\Constants::create('API_DIR', SRC_DIR.'api/');
$app->getComposerLoader()->addPsr4('Api\\', API_DIR);
