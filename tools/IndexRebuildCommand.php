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

class IndexRebuildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('db:index:rebuild')
            ->setDescription('Rebuild database indexes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Rebuilding indexes...');

        Application::coll('System')->deleteIndexes();

        Application::coll('User')->deleteIndexes();
        Application::coll('User')->ensureIndex(['uid' => 1], ['unique' => true]);
        Application::coll('User')->ensureIndex(['luser' => 1], ['unique' => true]);
        Application::coll('User')->ensureIndex(['lmail' => 1], ['unique' => true]);

        Application::coll('UserRole')->deleteIndexes();
        Application::coll('UserRole')->ensureIndex(['uid' => 1], ['unique' => true]);

        Application::coll('PermissionAllow')->deleteIndexes();
        Application::coll('PermissionAllow')->ensureIndex(['domain' => 1, 'val' => 1, 'role' => 1]);

        Application::coll('Role')->deleteIndexes();
        Application::coll('Role')->ensureIndex(['domain' => 1, 'name' => 1], ['unique' => true]);
        Application::coll('Role')->ensureIndex(['internal' => 1, 'name' => 1], ['unique' => true]);

        Application::coll('UserInfo')->deleteIndexes();
        Application::coll('UserInfo')->ensureIndex(['uid' => 1, 'domain' => 1], ['unique' => true]);

        Application::coll('Domain')->deleteIndexes();

        Application::coll('DomainLog')->deleteIndexes();

        Application::coll('Token')->deleteIndexes();
        Application::coll('Token')->ensureIndex(['purpose' => 1, 'identifier' => 1], ['unique' => true]);
        Application::coll('Token')->ensureIndex(['purpose' => 1, 'token' => 1]);
        Application::coll('Token')->ensureIndex(['expireat' => 1], ['expireAfterSeconds' => 0]);

        Application::coll('Session')->deleteIndexes();
        Application::coll('Session')->ensureIndex(['expireat' => 1], ['expireAfterSeconds' => 0]);

        Application::coll('RememberMeToken')->deleteIndexes();
        Application::coll('RememberMeToken')->ensureIndex(['uid' => 1, 'token' => 1]);
        Application::coll('RememberMeToken')->ensureIndex(['expireat' => 1], ['expireAfterSeconds' => 0]);

        Application::coll('LoginLog')->deleteIndexes();
        Application::coll('LoginLog')->ensureIndex(['uid' => 1, 'at' => -1]);

        Application::coll('Comment')->deleteIndexes();
        Application::coll('Comment')->ensureIndex(['ref' => 1, 'deleted' => 1, '_id' => 1]);

        Application::coll('Problem')->deleteIndexes();
        Application::coll('Problem')->ensureIndex(['owner' => 1, 'llink' => 1], ['unique' => true]);

        $output->writeln('<info>Done.</info>');
    }
}