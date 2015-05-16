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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use VJ\Core\Application;
use VJ\User\Importer\VijosImporter;

class UserImportCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('import:user')
            ->setDescription('Import user data from a data source')
            ->addOption('type', 't', InputOption::VALUE_REQUIRED,
                'Specify the type of the data source, currently only support --type vijos');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        if ($input->getOption('type') !== 'vijos') {
            $output->writeln('<error>Only support --type=vijos</error>');
            return;
        }

        if (Application::coll('User')->count() > 0) {
            $collectionToDrop = ['User', 'UserRole', 'UserInfo'];
            $q = new ConfirmationQuestion("User collection exists. Drop collections and continue? [y/n]", false);

            if ($helper->ask($input, $output, $q)) {
                foreach ($collectionToDrop as $collectionName) {
                    Application::coll($collectionName)->remove();
                    $output->writeln('<info>Dropped ' . $collectionName . '.</info>');
                }
            } else {
                return;
            }
        }

        $output->writeln('Testing connection to the data source...');

        try {
            $client = new \MongoClient();
            $db = $client->selectDB('vijos');
            $db->selectCollection('User')->count();
        } catch (\Exception $e) {
            $output->writeln('<error>Connect failed :' . $e->getMessage() . '</error>');
            return;
        }

        $count = 0;

        $output->writeln('Begin importing...');
        $importer = new VijosImporter($db);
        $importer->import(function ($uid, $username) use (&$output, &$count) {
            $output->writeln('Importing #' . $uid . ' ' . $username);
            $count++;
        });

        $output->writeln('<info>Imported ' . $count . ' users.</info>');
    }
}