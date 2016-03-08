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
class LoadCollectionData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $this->addCollection('Default Collection', 'default-collection');
    }

    public function addCollection($name, $slug, $context = 'default', $description = '', $enabled = true)
    {

        $collection = $this->getCollectionManager()->create();
        $collection->setName($name);
        $collection->setSlug($slug);
        $collection->setContext($this->getContext($context));
        $collection->setDescription($description);
        $collection->setEnabled($enabled);
        $this->getCollectionManager()->save($collection);

        $this->setReference(sprintf('collection_%s', $slug), $collection);
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
    public function getCollectionManager()
    {
        return $this->container->get('sonata.classification.manager.collection');
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
