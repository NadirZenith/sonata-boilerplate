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
use AppBundle\Entity\User\User;

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
        $menu->addChild($this->trans('Home'), array('route' => '_page_alias_home'));

        $menu->addChild($this->trans('Example Page'), array(
            'route' => 'page_slug',
            'routeParameters' => array(
                'path' => '/example'
            )
        ));


        $curr_locale = $this->container->get('request')->getLocale();
        $locale_selector = $menu->addChild($this->trans('Locale') . ' ' . $curr_locale, array(
            'uri' => '#',
            'attributes' => array('class' => 'dropdown'),
            'childrenAttributes' => array('class' => 'dropdown-menu'),
            'linkAttributes' => array('class' => 'dropdown-toggle', 'data-toggle' => 'dropdown', 'data-target' => '#'),
            /* 'label' => 'Products', */
            'extras' => array(
                'safe_label' => true,
            )
        ));
        $locales = [
            'pt', 'en', 'es'
        ];

        foreach ($locales as $locale) {
            $label = '';
            if ($curr_locale === $locale) {
                $label .= '-> ';
            }
            $locale_selector->addChild($label . $locale, array(
                'uri' => sprintf('?_locale=%s', $locale),
            ));
        }

        $user = $this->container->get('security.token_storage')->getToken()->getUser();

        if ($user instanceof User && $user->hasRole('ROLE_SUPER_ADMIN')) {
            $menu->addChild($this->trans('Admin'), array('route' => 'sonata_admin_dashboard'));
        }

        return $menu;
    }

    private function trans($msg)
    {
        return $this->container->get('translator')->trans($msg);
    }
}
