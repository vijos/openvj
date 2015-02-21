<?php
/**
 * This file is part of openvj project.
 *
 * Copyright 2013-2015 openvj dev team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VJ\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use VJ\Core\Application;

class ProblemTemplateImportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('init:problem:template')
            ->setDescription('Import problem template to database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $template = file_get_contents(__DIR__ . '/data/problem-template.md');

        Application::coll('System')->update([
            '_id' => 'ProblemTemplate'
        ], [
            '$set' => [
                'markdown' => $template
            ]
        ], [
            'upsert' => true
        ]);

        $output->writeln('<info>Problem template imported.</info>');
    }
}