<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadUserData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{

    private $container;

    public function load(ObjectManager $manager)
    {
        $this->addUser('dev', false, true, false, true);
        $this->addUser('user');
    }

    public function addUser($username, $email = false, $enabled = true, $locked = false, $superadmin = false)
    {

        $manager = $this->getUserManager();
        $faker = $this->getFaker();

        $user = $manager->createUser();
        $user->setUsername($username);
        $user->setPlainPassword($username);

        $mail = $email ? $email : $faker->safeEmail;
        $user->setEmail($mail);
        $user->setEnabled($enabled);
        $user->setLocked($locked);
        $user->setSuperAdmin($superadmin);

        $manager->updateUser($user);

        $this->setReference(sprintf('user_%s', $username), $user);
    }

    function getOrder()
    {
        return 1;
    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return \FOS\UserBundle\Model\UserManagerInterface
     */
    public function getUserManager()
    {
        return $this->container->get('fos_user.user_manager');
    }

    /**
     * @return \Faker\Generator
     */
    public function getFaker()
    {
        return $this->container->get('faker.generator');
    }
}
