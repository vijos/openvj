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

class ConfigInitializeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('config:init')
            ->setDescription('Initialize default config file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!file_exists(Application::$CONFIG_DIRECTORY . '/config.yml')) {
            copy(Application::$CONFIG_DIRECTORY . '/config.yml.default',
                Application::$CONFIG_DIRECTORY . '/config.yml');
            $output->writeln('<info>config.yml created.</info>');
        }

        if (!file_exists(Application::$CONFIG_DIRECTORY . '/db.yml')) {
            copy(Application::$CONFIG_DIRECTORY . '/db.yml.default',
                Application::$CONFIG_DIRECTORY . '/db.yml');
            $output->writeln('<info>db.yml created.</info>');
        }
    }
}