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

class DatabaseInitializeCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('db:init')
            ->setDescription('Initialize database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // 导入题目模板

        $output->writeln('Importing problem template...');
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

        // 创建全局域

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

        // 导入关键字

        $this->importKeyword($output, 'keywords-general-base64.txt', 'general');
        $this->importKeyword($output, 'keywords-name-base64.txt', 'name');
    }

    private function importKeyword(OutputInterface $output, $file, $field)
    {
        $output->writeln('Importing keyword ' . $file . '...');

        $file = base64_decode(file_get_contents(__DIR__ . '/data/' . $file));
        $keywords = [];
        $lines = explode("\n", $file);
        foreach ($lines as $line) {
            if (strlen($line) > 0) {
                $keywords[] = trim(mb_strtolower($line, 'UTF-8'));
            }
        }
        $keywords = array_values(array_unique($keywords));
        Application::coll('System')->update([
            '_id' => 'FilterKeyword'
        ], [
            '$set' => [
                strval($field) => $keywords
            ]
        ], [
            'upsert' => true
        ]);

        $output->writeln('<info>Imported ' . count($keywords) . ' keywords.</info>');
    }
}