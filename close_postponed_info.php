#!/usr/bin/env php
<?php

require __DIR__.'/vendor/autoload.php';

use Drupal\MassIssueUpdater\Command\ClosePostponedInfoApplication;

$application = new ClosePostponedInfoApplication();
$application->run();
