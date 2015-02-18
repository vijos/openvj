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
use Symfony\Component\Console\Question\Question;
use VJ\Core\Application;

class CertificateGenerateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('cert:generate')
            ->setDescription('Generate certificates');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        // ask fields
        $options = [
            'countryName' => 'CN',
            'stateOrProvinceName' => 'Shanghai',
            'localityName' => 'Shanghai'
        ];

        foreach ($options as $ask => $default) {
            $q = new Question($ask . '[' . $default . ']: ', $default);
            $options[$ask] = $helper->ask($input, $output, $q);
        }

        $output->writeln('<info>Generating CA private key...</info>');
        $CAPrivKey = new \Crypt_RSA();
        $key = $CAPrivKey->createKey(2048);
        file_put_contents(Application::$CONFIG_DIRECTORY . '/cert-ca.key', $key['privatekey']);

        $output->writeln('<info>Creating self-signed CA certificate...</info>');
        $CAPrivKey->loadKey($key['privatekey']);
        $pubKey = new \Crypt_RSA();
        $pubKey->loadKey($key['publickey']);
        $pubKey->setPublicKey();

        $subject = new \File_X509();
        $subject->setDNProp('id-at-organizationName', 'OpenVJ Certificate Authority');
        foreach ($options as $prop => $val) {
            $subject->setDNProp('id-at-' . $prop, $val);
        }
        $subject->setPublicKey($pubKey);

        $issuer = new \File_X509();
        $issuer->setPrivateKey($CAPrivKey);
        $issuer->setDN($CASubject = $subject->getDN());

        $x509 = new \File_X509();
        $x509->setStartDate('-1 month');
        $x509->setEndDate('+3 year');
        $x509->setSerialNumber(chr(1));
        $x509->makeCA();

        $result = $x509->sign($issuer, $subject, 'sha256WithRSAEncryption');
        file_put_contents(Application::$CONFIG_DIRECTORY . '/cert-ca.crt', $x509->saveX509($result));

        $output->writeln('<info>Generating API server cert private key...</info>');
        $privKey = new \Crypt_RSA();
        $key = $privKey->createKey(2048);
        file_put_contents(Application::$CONFIG_DIRECTORY . '/cert-api-server.key', $key['privatekey']);
        $privKey->loadKey($key['privatekey']);

        $output->writeln('<info>Creating API server certificate...</info>');
        $pubKey = new \Crypt_RSA();
        $pubKey->loadKey($key['publickey']);
        $pubKey->setPublicKey();

        $subject = new \File_X509();
        $subject->setPublicKey($pubKey);
        $subject->setDNProp('id-at-organizationName', 'OpenVJ API Server Certificate');
        foreach ($options as $prop => $val) {
            $subject->setDNProp('id-at-' . $prop, $val);
        }
        $subject->setDomain('127.0.0.1');

        $issuer = new \File_X509();
        $issuer->setPrivateKey($CAPrivKey);
        $issuer->setDN($CASubject);

        $x509 = new \File_X509();
        $x509->setStartDate('-1 month');
        $x509->setEndDate('+3 year');
        $x509->setSerialNumber(chr(1));

        $result = $x509->sign($issuer, $subject, 'sha256WithRSAEncryption');
        file_put_contents(Application::$CONFIG_DIRECTORY . '/cert-api-server.crt', $x509->saveX509($result));

        $output->writeln('<info>Generating API client cert private key...</info>');
        $privKey = new \Crypt_RSA();
        $key = $privKey->createKey(2048);
        file_put_contents(Application::$CONFIG_DIRECTORY . '/cert-api-client.key', $key['privatekey']);
        $privKey->loadKey($key['privatekey']);

        $output->writeln('<info>Creating API client certificate...</info>');
        $pubKey = new \Crypt_RSA();
        $pubKey->loadKey($key['publickey']);
        $pubKey->setPublicKey();

        $subject = new \File_X509();
        $subject->setPublicKey($pubKey);
        $subject->setDNProp('id-at-organizationName', 'OpenVJ API Client Certificate');
        foreach ($options as $prop => $val) {
            $subject->setDNProp('id-at-' . $prop, $val);
        }

        $issuer = new \File_X509();
        $issuer->setPrivateKey($CAPrivKey);
        $issuer->setDN($CASubject);

        $x509 = new \File_X509();
        $x509->setStartDate('-1 month');
        $x509->setEndDate('+3 year');
        $x509->setSerialNumber(chr(1));
        $x509->loadX509($x509->saveX509($x509->sign($issuer, $subject, 'sha256WithRSAEncryption')));
        $x509->setExtension('id-ce-keyUsage', array('digitalSignature', 'keyEncipherment', 'dataEncipherment'));
        $x509->setExtension('id-ce-extKeyUsage', array('id-kp-serverAuth', 'id-kp-clientAuth'));

        $result = $x509->sign($issuer, $x509, 'sha256WithRSAEncryption');
        file_put_contents(Application::$CONFIG_DIRECTORY . '/cert-api-client.crt', $x509->saveX509($result));
    }
}