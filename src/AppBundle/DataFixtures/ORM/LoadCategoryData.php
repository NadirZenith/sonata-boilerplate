<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Category fixtures loader.
 *
 * @author Sylvain Deloux <sylvain.deloux@ekino.com>
 */
class LoadCategoryData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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

        $this->addCategory('Default Category', 'default');

        //$manager->flush();
    }

    public function addCategory($name, $slug, $context = 'default', $enabled = true)
    {
        $category = $this->getCategoryManager()->create();
        $category->setName($name);
        $category->setSlug($slug);
        $category->setEnabled($enabled);
        $category->setContext($this->getReference(sprintf('context_%s', $context)));
        $this->getCategoryManager()->save($category);

        $this->setReference(sprintf('category_%s', $slug), $category);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Returns the Sonata MediaManager.
     *
     * @return \Sonata\CoreBundle\Model\ManagerInterface
     */
    public function getCategoryManager()
    {
        return $this->container->get('sonata.classification.manager.category');
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2;
    }
}
