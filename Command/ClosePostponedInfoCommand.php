<?php

/**
 * @file
 */

namespace Drupal\MassIssueUpdater\Command;

use Drupal\MassIssueUpdater\Scraper\IssueFinder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
/**
 *
 */
class ClosePostponedInfoCommand extends Command {
  /**
   *
   */
  protected function configure() {

    $this
      ->setName('close-postponed-info')
      ->setDescription('Close "Postponed: maintainer needs more info" issues that are older than 2 weeks')
      ->addArgument(
        'project',
        InputArgument::REQUIRED,
        'Project short name on drupal.org'
      );
  }

  /**
   *
   */
  protected function execute(InputInterface $input, OutputInterface $output) {

    $project = $input->getArgument('project');
    $helper = $this->getHelper('question');

    $username_question = new Question('drupal.org user name: ', 'klausi');
    $username = $helper->ask($input, $output, $username_question);

    $password_question = new Question('drupal.org password (hidden input): ');
    $password_question->setHidden(TRUE);
    $password = $helper->ask($input, $output, $password_question);

    $issue_finder = new IssueFinder();
    $issue_uris = $issue_finder
      ->setProject($project)
      ->setStatus(IssueFinder::STATUS_POSTPONED_NEEDS_MORE_INFO)
      ->setLastUpdatedBefore(strtotime('2 weeks ago'))
      ->findIssues();

    $issue_updater = new \Drupal\MassIssueUpdater\Scraper\IssueUpdater($issue_uris);
    $issue_updater
      ->setUser($username)
      ->setPassword($password)
      ->setStatus(IssueFinder::STATUS_CLOSED_WORKS_AS_DESIGNED)
      ->setComment('Closing due to lack of activity, feel fre to reopen with more information.')
      ->execute();
  }

}
