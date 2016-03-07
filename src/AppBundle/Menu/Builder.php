<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

/**
 * Class Builder
 *
 * @package Sonata\Bundle\DemoBundle\Menu
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class Builder extends ContainerAware
{

    /**
     * Creates the header menu
     *
     * @param FactoryInterface $factory
     * @param array            $options
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function mainMenu(FactoryInterface $factory, array $options)
    {


        $menuOptions = array_merge($options, array(
            'childrenAttributes' => array('class' => 'nav nav-pills'),
        ));

        $menu = $factory->createItem('main', $menuOptions);
        $menu->addChild('Home', array('route' => '_page_alias_home'));

        $menu->addChild('Example Page', array(
            'route' => 'page_slug',
            'routeParameters' => array(
                'path' => '/example'
            )
        ));

        $menu->addChild('Admin', array('route' => 'sonata_admin_dashboard'));

        return $menu;
    }
}
