<?php

namespace AppBundle\Command;

use Nz\OptionsBundle\Entity\Option;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Nz\CronBundle\Annotation\CronJob;

/**
 * @CronJob("PT20M")
 */
class CrawlIndexesCommand extends BaseCrawlCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('app:crawl:indexes');
        $this->setDescription('Crawl indexes Command');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        /*
          include_once 'nzdebug.php';
          df(time());
          $output->writeln('nice it works');
          return 'nice it works';
         */
        ini_set('max_execution_time', 600);

        $index_result = $this->crawlIndexes(false);

        /* $mail = $this->sendMail('Index result', $index_result); */
        $mail = 0;
        $output->writeln($index_result . ', Mail: ' . $mail);
        $this->sendAlert();

        return $index_result;
    }

    /**
     * Crawl Indexes
     */
    public function crawlIndexes($persist = false)
    {
        $handler = $this->getHandler();
        $clientPool = $this->getClientPool();
        $clients_indexes = $clientPool->getIndexClients();
        if (isset($clients_indexes['dynamic'])) {
            unset($clients_indexes['dynamic']);
        }
        $links = [];
        $errors = [];
        foreach ($clients_indexes as $client) {
            $l = $handler->handleIndexClient($client, $persist);

            $links = array_merge($links, $l);

            $e = $handler->getErrors();
            $errors = array_merge($errors, $e);
        }

        $msg = sprintf('Clients: %s, New Links: %s, Errors: %s', count($clients_indexes), count($links), count($errors));

        return $msg;
    }
}
