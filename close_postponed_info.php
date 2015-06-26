#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Drupal\MassIssueUpdater\Application\ClosePostponedInfoApplication;

/*
$command = new ClosePostponedInfoCommand();
$application = new Application();
$application->add($command);
$application->setDefaultCommand($command->getName());
$application->run();*/

$application = new ClosePostponedInfoApplication();
$application->run();

