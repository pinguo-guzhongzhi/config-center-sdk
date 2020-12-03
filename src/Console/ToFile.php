<?php
/**
 * Created by PhpStorm.
 * User: pinguokeji
 * Date: 2020/12/3
 * Time: 2:55 PM
 */

namespace PGConfig\Console;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ToFile extends Command
{
    protected function configure()
    {
        $this->setName('config:write:to:file')
            ->setDescription('get the config keys from config center and write them to file')
            ->addOption("url", "u", InputOption::VALUE_OPTIONAL, "the url of config center")
            ->addArgument("env", InputArgument::REQUIRED, "env: qa,prod")
            ->addArgument('clientId', InputArgument::REQUIRED, 'the id to identify yourself')
            ->addArgument('sec', InputArgument::REQUIRED, 'security key that get from config center')
            ->addArgument("fileName", InputArgument::REQUIRED, 'write the config to file')
            ->addArgument("type", InputArgument::OPTIONAL, "file type, allowed values: json,php,map-php", "json");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $clientId = $input->getArgument("clientId");
        $sec      = $input->getArgument("sec");
        $fileName = $input->getArgument("fileName");
        $type     = $input->getArgument("type");
        $env      = $input->getArgument("env");
        $url      = $input->getOption("url");

        try {
            $ins = \PGConfig\Client::NewInstance($clientId, $sec, $env);
            if ($url) {
                $ins->setUrl($url);
            }
            $rsp = $ins->loadConfig();
            if (!$rsp) {
                throw new \Exception("load remote config failed");
            }
            $str = "";
            switch ($type) {
                case "php":
                    $str = "<?php" . PHP_EOL . "return " . var_export($ins->toArray(), true) . ";" . PHP_EOL;
                    break;
                case "json":
                    $str = json_encode($ins->toDotKeyMap(), JSON_PRETTY_PRINT);
                    break;
                case "map-php":
                    $str = "<?php" . PHP_EOL . "return " . var_export($ins->toDotKeyMap(), true) . ";" . PHP_EOL;
                    break;
                default:
                    throw new \Exception("invalid type");
            }
            file_put_contents($fileName, $str);
        } catch (Exception $ex) {
            echo $ex->__toString(), PHP_EOL;
            exit(1);
        }
    }
}