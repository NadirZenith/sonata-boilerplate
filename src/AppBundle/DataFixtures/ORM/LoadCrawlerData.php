<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class LoadCollectionData
 *
 * @package Sonata\Bundle\EcommerceDemoBundle\DataFixtures\ORM
 *
 * @author  Hugo Briand <briand@ekino.com>
 */
class LoadCrawlerData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {

        $this->addCrawler('TaBonito', file_get_contents(__DIR__ . './../data/tabonito_config_crawler.yml'));
        $this->addCrawler('TaFixe', file_get_contents(__DIR__ . './../data/tafixe_config_crawler.yml'));
        $this->addCrawler('AiNanas', file_get_contents(__DIR__ . './../data/ainanas_config_crawler.yml'));
        $this->addCrawler('RaEvents', file_get_contents(__DIR__ . './../data/raevents_config_crawler.yml'));
        $this->addCrawler('SuperVaidosa', file_get_contents(__DIR__ . './../data/supervaidosa_config_crawler.yml'));
        $this->addCrawler('fHits (index only)', file_get_contents(__DIR__ . './../data/fhits_config_crawler.yml'));
        $this->addCrawler('Petiscos (category)', file_get_contents(__DIR__ . './../data/petiscos_config_crawler.yml'));
    }

    public function addCrawler($name, $config, $enabled = true)
    {

        $profile = $this->getProfileManager()->create();
        $profile->setName($name);
        $profile->setConfig($config);
        $profile->setEnabled($enabled);
        $this->getProfileManager()->save($profile);

        $this->setReference(sprintf('crawler_profile_%s', $name), $profile);
    }

    public function getContext($id)
    {
        return $this->getReference(sprintf('context_%s', $id));
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns the Sonata CollectionManager.
     *
     * @return \Sonata\CoreBundle\Model\ManagerInterface
     */
    public function getProfileManager()
    {
        return $this->container->get('nz.crawler.manager.profile');
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10;
    }
}
