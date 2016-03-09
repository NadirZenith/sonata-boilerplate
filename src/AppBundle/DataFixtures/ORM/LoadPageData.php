<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Sonata\PageBundle\Model\SiteInterface;
use Sonata\PageBundle\Model\PageInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LoadPageData extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{

    private $container;

    public function load(ObjectManager $manager)
    {

        $site = $this->createSite();
        $this->createGlobalPage($site);
        $this->createHomePage($site);
        $this->create404ErrorPage($site);
        $this->create500ErrorPage($site);
        $this->createExamplePage($site);
    }

    /**
     * @return SiteInterface $site
     */
    public function createSite()
    {
        $site = $this->getSiteManager()->create();

        $site->setHost('localhost');
        $site->setEnabled(true);
        $site->setName('localhost');
        $site->setEnabledFrom(new \DateTime('now'));
        $site->setEnabledTo(new \DateTime('+10 years'));
        $site->setRelativePath("");
        $site->setIsDefault(true);

        $this->getSiteManager()->save($site);

        return $site;
    }

    /**
     * @param SiteInterface $site
     */
    public function createGlobalPage(SiteInterface $site)
    {
        $pageManager = $this->getPageManager();
        $blockManager = $this->getBlockManager();
        $blockInteractor = $this->getBlockInteractor();

        $global = $pageManager->create();
        $global->setName('global');
        $global->setRouteName('_page_internal_global');
        $global->setSite($site);

        $pageManager->save($global);

        // CREATE A HEADER BLOCK
        $global->addBlocks($header = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $global,
            'code' => 'header',
        )));

        $header->setName('The header container');
        $header->addChildren($text = $blockManager->create());

        //logo block
        $text->setType('sonata.block.service.text');
        $text->setSetting('content', '<h2><a href="/">Sonata Demo</a></h2>');
        $text->setPosition(1);
        $text->setEnabled(true);
        $text->setPage($global);

        //2nd header wrapper
        $global->addBlocks($headerTop = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $global,
            'code' => 'header-top',
            ), function ($container) {
            $container->setSetting('layout', '<div class="pull-right">{{ CONTENT }}</div>');
        }));

        $headerTop->setPosition(1);

        $header->addChildren($headerTop);

        //account block
        $headerTop->addChildren($account = $blockManager->create());

        $account->setType('sonata.user.block.account');
        $account->setPosition(1);
        $account->setEnabled(true);
        $account->setPage($global);

        //menu wrapper
        $global->addBlocks($headerMenu = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $global,
            'code' => 'header-menu',
        )));

        $headerMenu->setPosition(2);
        $header->addChildren($headerMenu);
        $headerMenu->setName('The header menu container');
        $headerMenu->setPosition(3);

        //menu
        $headerMenu->addChildren($menu = $blockManager->create());
        $menu->setType('sonata.block.service.menu');
        $menu->setSetting('menu_name', "AppBundle:Builder:mainMenu");
        $menu->setSetting('safe_labels', true);
        $menu->setPosition(3);
        $menu->setEnabled(true);
        $menu->setPage($global);

        //footer
        $global->addBlocks($footer = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $global,
            'code' => 'footer'
            ), function ($container) {
            $container->setSetting('layout', '<div class="row page-footer well">{{ CONTENT }}</div>');
        }));

        $footer->setName('The footer container');

        // Footer : add 2 children block containers (left, right)
        $footer->addChildren($footerLinksLeft = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $global,
            'code' => 'content',
            ), function ($container) {
            $container->setSetting('layout', '<div class="col-md-6">{{ CONTENT }}</div>');
        }));


        $footer->addChildren($footerLinksRight = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $global,
            'code' => 'content'
            ), function ($container) {
            $container->setSetting('layout', '<div class="col-md-6">{{ CONTENT }}</div>');
        }));

        $links = <<<CONTENT
            <h4>LINKS</h4>
            <ul class="links">
                <li><a href="/example">Example</a></li>
                <li><a href="/example">Example</a></li>
                <li><a href="/example">Example</a></li>
            </ul>
CONTENT;

        // Footer left links
        $footerLinksLeft->addChildren($text = $blockManager->create());

        $text->setType('sonata.block.service.text');
        $text->setSetting('content', $links);

        $text->setPosition(1);
        $text->setEnabled(true);
        $text->setPage($global);

        // Footer right links
        $footerLinksRight->addChildren($text = $blockManager->create());

        $text->setType('sonata.block.service.text');
        $text->setSetting('content', $links);

        $text->setPosition(1);
        $text->setEnabled(true);
        $text->setPage($global);

        $pageManager->save($global);
    }

    /**
     * @param SiteInterface $site
     */
    public function createHomePage(SiteInterface $site)
    {
        $pageManager = $this->getPageManager();
        $blockManager = $this->getBlockManager();
        $blockInteractor = $this->getBlockInteractor();

        $this->addReference('page-homepage', $homepage = $pageManager->create());
        $homepage->setSlug('/');
        $homepage->setUrl('/');
        $homepage->setName('Home');
        $homepage->setPageAlias('home');
        $homepage->setTitle('Homepage');
        $homepage->setEnabled(true);
        $homepage->setDecorate(0);
        $homepage->setRequestMethod('GET|POST|HEAD|DELETE|PUT');
        $homepage->setTemplateCode('2columns');
        $homepage->setRouteName(PageInterface::PAGE_ROUTE_CMS_NAME);
        $homepage->setSite($site);

        // left container
        $homepage->addBlocks($left = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $homepage,
            'code' => 'left_col',
        )));

        $left->setName('The left container');
        $left->addChildren($text = $blockManager->create());

        // left block
        $text->setType('sonata.block.service.text');
        $text->setSetting('content', '<h2>Left content</h2>');
        $text->setPosition(1);
        $text->setEnabled(true);
        $text->setPage($homepage);

        // right container
        $homepage->addBlocks($right = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $homepage,
            'code' => 'right_col',
        )));
        $right->setName('The right container');
        $right->addChildren($text = $blockManager->create());

        // right block
        $text->setType('sonata.block.service.text');
        $text->setSetting('content', '<h2>Right content</h2>');
        $text->setPosition(1);
        $text->setEnabled(true);
        $text->setPage($homepage);


        $pageManager->save($homepage);
    }

    public function create404ErrorPage(SiteInterface $site)
    {
        $pageManager = $this->getPageManager();
        $blockManager = $this->getBlockManager();
        $blockInteractor = $this->getBlockInteractor();

        $page = $pageManager->create();
        $page->setName('_page_internal_error_not_found');
        $page->setTitle('Error 404');
        $page->setEnabled(true);
        $page->setDecorate(1);
        $page->setRequestMethod('GET|POST|HEAD|DELETE|PUT');
        $page->setTemplateCode('default');
        $page->setRouteName('_page_internal_error_not_found');
        $page->setSite($site);

        $page->addBlocks($block = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $page,
            'code' => 'content_top',
        )));

        // add the breadcrumb
        $block->addChildren($breadcrumb = $blockManager->create());
        $breadcrumb->setType('sonata.page.block.breadcrumb');
        $breadcrumb->setPosition(0);
        $breadcrumb->setEnabled(true);
        $breadcrumb->setPage($page);

        // Add text content block
        $block->addChildren($text = $blockManager->create());
        $text->setType('sonata.block.service.text');
        $text->setSetting('content', '<h2>Error 404</h2><div>Page not found.</div>');
        $text->setPosition(1);
        $text->setEnabled(true);
        $text->setPage($page);

        $pageManager->save($page);
    }

    public function create500ErrorPage(SiteInterface $site)
    {
        $pageManager = $this->getPageManager();
        $blockManager = $this->getBlockManager();
        $blockInteractor = $this->getBlockInteractor();

        $page = $pageManager->create();
        $page->setName('_page_internal_error_fatal');
        $page->setTitle('Error 500');
        $page->setEnabled(true);
        $page->setDecorate(1);
        $page->setRequestMethod('GET|POST|HEAD|DELETE|PUT');
        $page->setTemplateCode('default');
        $page->setRouteName('_page_internal_error_fatal');
        $page->setSite($site);

        $page->addBlocks($block = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $page,
            'code' => 'content_top',
        )));

        // add the breadcrumb
        $block->addChildren($breadcrumb = $blockManager->create());
        $breadcrumb->setType('sonata.page.block.breadcrumb');
        $breadcrumb->setPosition(0);
        $breadcrumb->setEnabled(true);
        $breadcrumb->setPage($page);

        // Add text content block
        $block->addChildren($text = $blockManager->create());
        $text->setType('sonata.block.service.text');
        $text->setSetting('content', '<h2>Error 500</h2><div>Internal error.</div>');
        $text->setPosition(1);
        $text->setEnabled(true);
        $text->setPage($page);

        $pageManager->save($page);
    }

    /**
     * Creates example page
     *
     * @param SiteInterface $site
     *
     * @return void
     */
    public function createExamplePage(SiteInterface $site)
    {
        $this->createTextContentPage($site, 'example', 'Example Page', <<<CONTENT
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut quis sapien gravida, eleifend diam id, vehicula erat. Aenean ultrices facilisis tellus. Vivamus vitae molestie diam. Donec quis mi porttitor, lobortis ipsum quis, fermentum dui. Donec nec nibh nec risus porttitor pretium et et lorem. Nullam mauris sapien, rutrum sed neque et, convallis ullamcorper lacus. Nullam vehicula a lectus vel suscipit. Nam gravida faucibus fermentum.</p>
<p>Pellentesque dapibus eu nisi quis adipiscing. Phasellus adipiscing turpis nunc, sed interdum ante porta eu. Ut tempus, purus posuere molestie cursus, quam nisi fermentum est, dictum gravida nulla turpis vel nunc. Maecenas eget sem quam. Nam condimentum mi id lectus venenatis, sit amet semper purus convallis. Nunc ullamcorper magna mi, non adipiscing velit semper quis. Duis vel justo libero. Suspendisse laoreet hendrerit augue cursus congue. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae;</p>
<p>Nullam dignissim sapien vestibulum erat lobortis, sed imperdiet elit varius. Fusce nisi eros, feugiat commodo scelerisque a, lacinia et quam. In neque risus, dignissim non magna non, ultricies faucibus elit. Vivamus in facilisis enim, porttitor volutpat justo. Praesent placerat feugiat nibh et fermentum. Vivamus eu fermentum metus. Sed mattis volutpat quam a suscipit. Donec blandit sagittis est, ac tristique arcu venenatis sed. Fusce vel libero id lectus aliquet sollicitudin. Fusce ultrices porta est, non pellentesque lorem accumsan eget. Fusce id libero sit amet nulla venenatis dapibus. Maecenas fermentum tellus eu magna mollis gravida. Nam non nibh magna.</p>
CONTENT
        );
    }

    /**
     * Creates simple content pages
     *
     * @param SiteInterface $site    A Site entity instance
     * @param string        $url     A page URL
     * @param string        $title   A page title
     * @param string        $content A text content
     *
     * @return void
     */
    public function createTextContentPage(SiteInterface $site, $url, $title, $content)
    {
        $pageManager = $this->getPageManager();
        $blockManager = $this->getBlockManager();
        $blockInteractor = $this->getBlockInteractor();

        $page = $pageManager->create();
        $page->setSlug(sprintf('/%s', $url));
        $page->setUrl(sprintf('/%s', $url));
        $page->setName($title);
        $page->setTitle($title);
        $page->setEnabled(true);
        $page->setDecorate(1);
        $page->setRequestMethod('GET|POST|HEAD|DELETE|PUT');
        $page->setTemplateCode('default');
        $page->setRouteName('page_slug');
        $page->setSite($site);
        $page->setParent($this->getReference('page-homepage'));

        $page->addBlocks($block = $blockInteractor->createNewContainer(array(
            'enabled' => true,
            'page' => $page,
            'code' => 'content_top',
        )));

        // add the breadcrumb
        $block->addChildren($breadcrumb = $blockManager->create());
        $breadcrumb->setType('sonata.page.block.breadcrumb');
        $breadcrumb->setPosition(0);
        $breadcrumb->setEnabled(true);
        $breadcrumb->setPage($page);

        // Add text content block
        $block->addChildren($text = $blockManager->create());
        $text->setType('sonata.block.service.text');
        $text->setSetting('content', sprintf('<h2>%s</h2><div>%s</div>', $title, $content));
        $text->setPosition(1);
        $text->setEnabled(true);
        $text->setPage($page);

        $pageManager->save($page);
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
     * @return \Sonata\PageBundle\Model\SiteManagerInterface
     */
    public function getSiteManager()
    {
        return $this->container->get('sonata.page.manager.site');
    }

    /**
     * @return \Sonata\PageBundle\Model\PageManagerInterface
     */
    public function getPageManager()
    {
        return $this->container->get('sonata.page.manager.page');
    }

    /**
     * @return \Sonata\BlockBundle\Model\BlockManagerInterface
     */
    public function getBlockManager()
    {
        return $this->container->get('sonata.page.manager.block');
    }

    /**
     * @return \Faker\Generator
     */
    public function getFaker()
    {
        return $this->container->get('faker.generator');
    }

    /**
     * @return \Sonata\PageBundle\Entity\BlockInteractor
     */
    public function getBlockInteractor()
    {
        return $this->container->get('sonata.page.block_interactor');
    }

    public function getOrder()
    {
        return 10;
    }
}
