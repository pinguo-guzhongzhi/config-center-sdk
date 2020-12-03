<?php
/**
 * Created by PhpStorm.
 * User: pinguokeji
 * Date: 2020/12/3
 * Time: 11:39 AM
 */

namespace PGConfig\console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class Config extends Command
{
    protected function configure()
    {
        $this->setName('config:list')
            ->setDescription('get the config keys from config center')
            ->setHelp('This command allow you to create models...')
            ->addArgument('clientId', InputArgument::REQUIRED, 'the id to identify yourself')
            ->addArgument('sec', InputArgument::REQUIRED, 'security key that get from config center');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clientId = $input->getArgument("clientId");
        $sec      = $input->getArgument("sec");
        try {
            echo date("Y-m-d H:i:s"), PHP_EOL;
            $ins  = \PGConfig\Client::NewInstance($clientId, $sec, \PGConfig\Client::ENV_QA);
            $data = $ins->loadConfig();
            print_r($data);
        } catch (Exception $ex) {
            echo $ex->__toString(), PHP_EOL;
        }
    }
}