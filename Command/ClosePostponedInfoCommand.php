<?php

namespace Drupal\MassIssueUpdater\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClosePostponedInfoCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('close-postponed-info')
            ->setDescription('Close "Postponed: maintainer needs more info" issues that are older than 2 weeks')
            ->addArgument(
                'project',
                InputArgument::REQUIRED,
                'Project short name on drupal.org'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $project = $input->getArgument('project');

        $output->writeln($project);
    }
}
