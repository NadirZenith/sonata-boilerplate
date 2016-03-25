<?php

namespace AppBundle\CrawlerClients;

use Symfony\Component\DomCrawler\Crawler;
use Nz\CrawlerBundle\Model\LinkInterface;

class TabonitoClient extends BaseClient
{

    function configure(LinkInterface $link, array $config = array())
    {
        parent::configure($link, $config);

        $this->article_base_filter = '#block-system-main > div > div > div > div > div.panels-flexible-row.panels-flexible-row-post-inner-main-row.panels-flexible-row-first.clearfix > div > div > div';
        $this->base_domain = $this->baseurl = 'http://www.tabonito.pt/';
        $this->index_link_filter = 'body div.view-content h2 a';
        $this->start_page = 0;
        $this->limit_pages = 2;
    }

    /**
     *  {@inheritdoc}
     */
    function saveClientProfile(Crawler $entity_crawler)
    {
        $this->setItem('title', trim($entity_crawler->filter('h1')->text()));

        $this->setItem('content', $this->getArrayValues($entity_crawler->filter('.field-items p')), TRUE);
        /*
         */
        $images = $this->getArrayAttributes($entity_crawler->filter('div.field-items img'), 'src');
        $iframes = $this->getArrayAttributes($entity_crawler->filter('iframe'), 'src');
        $medias = $this->filterContent(array_merge($images, $iframes));

        $this->setItem('medias', $this->matchMedias($medias));
    }

    public function getNextPageUrl($current_page)
    {
        $url = $this->baseurl . 'inicio?page=' . $current_page;
        return $url;
    }

    /**
     *  {@inheritdoc}
     */
    protected function stringsToFilter()
    {
        return [
            'deco-proteste',
            'wone',
        ];

        //old entity filter
        return array_merge(parent::stringsToFilter(), [
            'Tá Bonito',
            'Deco Proteste',
            //  '/sites/all/modules/contrib/smiley/packs/moskis/icon_surprised.png',
            // '/sites/all/modules/contrib/smiley/packs/moskis/icon_smile.png',
            '/sites/all/modules/contrib/smiley/packs/moskis/',
            'wone',
            'http://www.tabonito.pt/sites/default/files/related_images/decob.jpg'
            ]
        );
    }
}
