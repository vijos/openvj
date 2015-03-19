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
use VJ\User\DomainUtil;
use VJ\VJ;

class SystemInitializeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('init:sys')
            ->setDescription('Initialize system variables');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Creating global domain...');
        try {
            $doc = [
                '_id' => DomainUtil::getGlobalDomainId(),
                'name' => 'OpenVJ',
                'owner' => VJ::USER_ID_SYSTEM,
                'at' => new \MongoDate(),
            ];
            Application::coll('Domain')->insert($doc);
        } catch (\Exception $e) {
        }
    }
}