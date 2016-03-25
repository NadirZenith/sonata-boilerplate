<?php

namespace AppBundle\Command;

use Nz\OptionsBundle\Entity\Option;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Nz\CronBundle\Annotation\CronJob;

/**
 * @CronJob("PT30S")
 */
class CrawlLinksCommand extends BaseCrawlCommand
{

    /**
     * {@inheritdoc}
     */
    public function configure()
    {
        $this->setName('app:crawl:links');
        $this->setDescription('Crawl links Command');
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        ini_set('max_execution_time', 0);

        $entity_result = $this->crawlLinks(true);
        /*$entity_result = 'blablalb';*/

         /*$mail = $this->sendMail('Links result', $entity_result); */
         $this->sendAlert(); 
        $mail = 0;

        $output->writeln($entity_result . ', Mail: ' . $mail);

        return$entity_result;
    }

    /**
     * Crawl links
     */
    public function crawlLinks($persist = false)
    {
        $linkManager = $this->getLinkManager();
        $handler = $this->getHandler();
        $clientPool = $this->getClientPool();

        $links = $linkManager->findLinksForProcess(4);
        $errors = [];
        $entities = [];
        foreach ($links as $link) {
            $client = $clientPool->getEntityClientForLink($link);
            if ($client) {
                $entity = $handler->handleEntityClient($client, $persist);

                if (!$entity) {
                    $notes = $link->getNotes();
                    $errors[] = end($notes);
                } else {
                    $entities[] = $entity->getTitle();
                }
            } else {
                $errors[] = sprintf('No Entity Client for link url: %s', $link->getUrl());
            }
        }

        $msg = sprintf('Links: %s, Success: %s, Errors: %s', count($links), count($entities), count($errors));

        return $msg;
    }
}
