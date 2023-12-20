<?php

namespace App\Controller;

use App\Entity\PepperPrices;
use App\Helpers\DatabaseHelper;
use App\Service\PropertyApi;
use DOMDocument;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;


class CommandsController extends AbstractController
{

    /**
     * @Route("no_auth/getmarketprices")
     */
    public function getmarketprices(LoggerInterface $logger, EntityManagerInterface $entityManager): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $peppersArray[] = array();
        if (function_exists('exec')) {
            echo "exec is enabled";
        } else {
            echo "exec is not enabled";
        }

        $currentDirectory = getcwd();
        $filename = $currentDirectory . '\\..\\src\\controller\\index.html'; // Replace 'example.txt' with the name of the file you want to delete

        if (file_exists($filename)) {
            if (unlink($filename)) {
                echo "File '$filename' has been deleted.";
            } else {
                echo "Unable to delete file '$filename'.";
            }
        } else {
            $responseArray[] = array(

                'result_code' => "File '$filename' does not exist."
            );

        }

        $command = 'wget --no-check-certificate https://durbanmarkets.durban.gov.za/';
        $this->execute($command);

        if (file_exists($filename)) {
            $contents = file_get_contents($filename);

            if ($contents !== false) {
                $logger->info("content found");

                $html = file_get_contents($filename); // Replace 'example.html' with your HTML file

                $rows = explode('<tr', $html);
                foreach ($rows as $row) {
                    //$logger->info("row: " . $row);
                    if ((str_contains($row, 'PEPPERS RED') || str_contains($row, 'PEPPERS YELLOW')) && !str_contains($row, 'option')) {
                        $cells = explode('<td', $row);
                        $logger->info("cells: " . print_r($cells, true));
                        $commodity = 'PEPPERS YELLOW';
                        if (str_contains($row, 'PEPPERS RED')) {
                            $commodity = 'PEPPERS RED';
                        }

                        $weight = (str_replace('</td>', "",$cells[2]));
                        $weight = (str_replace('>', "",$weight));
                        $low = (str_replace('</td>', "",$cells[6]));
                        $low = (str_replace('>', "",$low));
                        $high = (str_replace('</td>', "",$cells[7]));
                        $high = (str_replace('>', "",$high));
                        $average = (str_replace('</td>', "",$cells[8]));
                        $average = (str_replace('>', "",$average));
                        $salesTotal = (str_replace('</td>', "",$cells[9]));
                        $salesTotal = (str_replace('>', "",$salesTotal));
                        $totalKgSold = (str_replace('</td>', "",$cells[10]));
                        $totalKgSold = (str_replace('>', "",$totalKgSold));

                        $logger->info("weight: " . doubleval($weight));
                        $logger->info("low: " .  doubleval($low));
                        $logger->info("high: " .  doubleval($high));
                        $logger->info("average: " .  doubleval($average));
                        $logger->info("salesTotal: " .  intval($salesTotal));
                        $logger->info("totalKgSold: " . intval($totalKgSold));

                        if(intval($totalKgSold)>0){
                            $pepperPrice = new PepperPrices();
                            $pepperPrice->setCommodity($commodity);
                            $pepperPrice->setWeight(doubleval($weight));
                            $pepperPrice->setLow(doubleval($low));
                            $pepperPrice->setHigh(doubleval($high));
                            $pepperPrice->setAverage(doubleval($average));
                            $pepperPrice->setSalesTotal(intval($salesTotal));
                            $pepperPrice->setTotalKgSold(intval($totalKgSold));
                            $pepperPrice->setDate(new \DateTime());
                            $logger->info("pepper object " . print_r($pepperPrice));
                            $entityManager->persist($pepperPrice);
                        }
                    } else {
                        $logger->debug("No peppers found");
                    }
                }
                $entityManager->flush();
                $logger->info("peppersArray: " . print_r($peppersArray, true));
            } else {
                $logger->info("Unable to read file '$filename'.");
            }
        } else {
            $logger->error("File '$filename' does not exist.");
        }


        $responseArray[] = array(
            'result_code' => "File '$filename' does not exist."
        );

        return new JsonResponse($responseArray, 200, array());
    }

    /**
     * @Route("no_auth/runcommand/clear")
     */
    public function clearSymfony(LoggerInterface $logger): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (function_exists('exec')) {
            echo "exec is enabled";
        } else {
            echo "exec is not enabled";
        }

        $command = 'php ../bin/console doctrine:cache:clear-metadata';
        $result = $this->execute($command);
        $responseArray[] = array(
            'command' => $command,
            'result_message' => print_r($result, true),
            'result_code' => 0
        );

        $command = 'php ../bin/console doctrine:cache:clear-query';
        $result = $this->execute($command);
        $responseArray[] = array(
            'command' => $command,
            'result_message' => print_r($result, true),
            'result_code' => 0
        );

        $command = 'php ../bin/console doctrine:cache:clear-result';
        $result = $this->execute($command);
        $responseArray[] = array(
            'command' => $command,
            'result_message' => print_r($result, true),
            'result_code' => 0
        );
        return new JsonResponse($responseArray, 200, array());
    }

    /**
     * @Route("no_auth/runcommand/downlaod/dependencies")
     */
    public function downloadDependencies(LoggerInterface $logger): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (function_exists('exec')) {
            echo "exec is enabled";
        } else {
            echo "exec is not enabled";
        }

        $command = 'php composer.phar install';
        $result = $this->execute($command);
        $responseArray[] = array(
            'command' => $command,
            'result_message' => print_r($result, true),
            'result_code' => 0
        );
        return new JsonResponse($responseArray, 200, array());
    }

    /**
     * @Route("no_auth/runcommand/phpmemory")
     */
    public function checkPHPMemory(LoggerInterface $logger): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $command = 'php -i | grep "memory_limit"';
        $result = $this->execute($command);
        $responseArray[] = array(
            'command' => $command,
            'result_message' => print_r($result, true),
            'result_code' => 0
        );

        return new JsonResponse($responseArray, 200, array());
    }

    /**
     * @Route("no_auth/runcommand/gitversion")
     */
    public function gitVersion(LoggerInterface $logger): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $logger->info("Server name: " . $_SERVER['SERVER_NAME']);
        $command = 'git --version';
        $result = $this->execute($command);
        $responseArray[] = array(
            'command' => $command,
            'result_message' => print_r($result, true),
            'result_code' => 0
        );

        return new JsonResponse($responseArray, 200, array());
    }

    /**
     * @Route("no_auth/runcommand/gitpull")
     */
    public function gitPull(LoggerInterface $logger): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        try {
            $command = 'git config --global user.email nkosi.benedict@gmail.com';
            $result = $this->execute($command);
            $responseArray[] = array(
                'command' => $command,
                'result_message_auto' => print_r($result, true),
                'result_code' => 0
            );

            $command = 'git config --global user.name nkosibenedict';
            $result = $this->execute($command);
            $responseArray[] = array(
                'command' => $command,
                'result_message_auto' => print_r($result, true),
                'result_code' => 0
            );


            $command = 'git stash';
            $result = $this->execute($command);
            $responseArray[] = array(
                'command' => $command,
                'result_message_auto' => print_r($result, true),
                'result_code' => 0
            );

            $command = 'git fetch --all';
            $result = $this->execute($command);
            $responseArray[] = array(
                'command' => $command,
                'result_message_auto' => print_r($result, true),
                'result_code' => 0
            );


            $server = $_SERVER['SERVER_NAME'];

            $command = 'git reset --hard origin/master';


            $result = $this->execute($command);
            $responseArray[] = array(
                'command' => $command,
                'result_message_auto' => print_r($result, true),
                'result_code' => 0,
                'server' => $server
            );
            return new JsonResponse($responseArray, 200, array());
        } catch (Exception $ex) {
            $logger->error($ex->getMessage() . ' - ' . __METHOD__ . ':' . $ex->getLine() . ' ' . $ex->getTraceAsString());
        }
        return new JsonResponse($responseArray, 200, array());
    }

    /**
     * @Route("no_auth/runcommand/gitstash")
     */
    public function gitStash(LoggerInterface $logger): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $responseArray = array();
        $command = 'git stash';
        $result = $this->execute($command);
        $responseArray[] = array(
            'command' => $command,
            'result_message_auto' => print_r($result, true),
            'result_code' => 0
        );

        return new JsonResponse($responseArray, 200, array());
    }


    /**
     * @Route("no_auth/runcommand/phpinfo")
     */
    public function phpinfo(LoggerInterface $logger): Response
    {
        if ($this->container->has('profiler')) {
            $this->container->get('profiler')->disable();
        }
        ob_start();
        phpinfo();
        $str = ob_get_contents();
        ob_get_clean();

        return new Response($str);
    }

    /**
     * @Route("no_auth/runcommand/mysqldump")
     */
    public function mysql(LoggerInterface $logger): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        $command = 'mysql --version';
        $result = $this->execute($command);
        $responseArray[] = array(
            'command' => $command,
            'result_message' => print_r($result, true),
            'result_code' => 0
        );

        return new JsonResponse($responseArray, 200, array());
    }

    /**
     * Executes a command and reurns an array with exit code, stdout and stderr content
     * @param string $cmd - Command to execute
     * @param string|null $workdir - Default working directory
     * @return string[] - Array with keys: 'code' - exit code, 'out' - stdout, 'err' - stderr
     */
    function execute($cmd, $workdir = null)
    {

        if (is_null($workdir)) {
            $workdir = __DIR__;
        }

        $descriptorspec = array(
            0 => array("pipe", "r"),  // stdin
            1 => array("pipe", "w"),  // stdout
            2 => array("pipe", "w"),  // stderr
        );

        $process = proc_open($cmd, $descriptorspec, $pipes, $workdir, null);

        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);

        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[2]);

        return [
            'code' => proc_close($process),
            'out' => trim($stdout),
            'err' => trim($stderr),
        ];
    }

    /**
     * @Route("no_auth/runcommand/deleteallrooms")
     */


}