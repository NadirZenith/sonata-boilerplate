<?php

namespace AppBundle\Command;

use Nz\OptionsBundle\Entity\Option;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use ColourStream\Bundle\CronBundle\Annotation\CronJob;
use Nz\CrawlerBundle\Command\BaseCrawlCommand as CrawlCommand;

/**
 */
abstract class BaseCrawlCommand extends CrawlCommand
{

    /**
     * Send arduino alert
     * 
     */
    protected function sendAlert($alert = null)
    {
        $url = 'http://192.168.0.111/?button1on';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpcode >= 200 && $httpcode < 300) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Send email
     * 
     */
    protected function sendMail($subject, $msg)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom('albertino05@gmail.com')
            ->setTo('albertino05@gmail.com')
            ->setBody(
            $msg, 'text/html'
            )
        /*
         * If you also want to include a plaintext version of the message
          ->addPart(
          $this->renderView(
          'Emails/registration.txt.twig',
          array('name' => $name)
          ),
          'text/plain'
          )
         */
        ;
        return $this->getMailer()->send($message);
    }

    /**
     * Get Mailer
     * 
     * @return Mailer
     */
    protected function getMailer()
    {
        return $this->getContainer()->get('mailer');
    }
}
