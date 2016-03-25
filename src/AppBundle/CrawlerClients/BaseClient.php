<?php

namespace AppBundle\CrawlerClients;

use Nz\CrawlerBundle\Client\BaseClient as BaseCrawlerClient;
use Symfony\Component\DomCrawler\Crawler;
use Nz\CrawlerBundle\Model\LinkInterface;
use AppBundle\Entity\Media\Media;
use Nz\CrawlerBundle\Client\EntityClientException;
use AppBundle\Entity\Media\Gallery;
use AppBundle\Entity\Media\GalleryHasMedia;

abstract class BaseClient extends BaseCrawlerClient
{

    protected $mediaManager;
    protected $mediaPool;

    /**
     * Set entity defaults
     * 
     * @param object $entity The entity
     * 
     * @return object $entity The entity
     * 
     * */
    protected function setEntityDefaults($entity)
    {

        $entity->setEnabled(true);
        return $entity;
    }

    /**
     * Normalize clrawled profile to entity
     * 
     * @param object $entity The entity
     * 
     * @return object $entity The normalized entity

     */
    function normalizeEntity($entity)
    {
        $entity = $this->setEntityDefaults($entity);

        $title = $this->getItem('title', true);
        $entity->setTitle($title);

        $this->normalizeMedias($this->getItem('medias', true), $entity);
        //content && img && iframe content
        $content = '';
        $abstract = '';
        foreach ($this->getItem('content') as $p) {
            $content .= sprintf('<p>%s</p>', $p);
            $abstract .= $p;
        }

        $entity->setContent($content);

        $abstract_item = $this->getItem('abstract');
        if ($abstract_item) {
            $entity->setExcerpt($abstract_item);
        } else {

            $entity->setExcerpt($this->truncate($abstract));
        }

        return $entity;
    }

    /**
     * Normalize clrawled media to Media entity
     * 
     * @param array $medias The arrray of medias
     * you can get medias from class property too
     * 
     * @param object $entity The holding entity
     * 
     * @return object $entity The holding entity 

     */
    protected function normalizeMedias(array $medias, $entity)
    {
        $context = 'crawl';

        $gallery = new Gallery();
        $gallery->setContext($context);
        $gallery->setEnabled(true);
        $gallery->setName($entity->getTitle());
        $gallery->setDefaultFormat('admin');

        $count = 1;
        $galleryHasMedias = [];
        foreach ($medias as $med) {
            $media = new Media();
            $media->setName($entity->getTitle());
            $media->setCategory($this->getCategory($context));
            $media->setContext($context);
            $media->setEnabled(true);
            $media->setProviderName($med['provider']);

            /* d($this->getCategory($context)); */
            /* dd($entity); */
            $temp_file = false;

            if ($med['provider'] === 'sonata.media.provider.image') {

                $ext = pathinfo($med['url'], PATHINFO_EXTENSION);
                $temp_file = rtrim(sys_get_temp_dir(), '/') . '/' . uniqid() . '.' . $ext;
                $file_content = file_get_contents($med['url']);
                file_put_contents($temp_file, $file_content);
                $media->setBinaryContent($temp_file);
            } else {

                $media->setBinaryContent($med['id']);
            }


            if ($count === 1) {

                $entity->setImage($media);
            } else {

                $galleryHasMedia = new GalleryHasMedia();
                $galleryHasMedia->setGallery($gallery);
                $galleryHasMedia->setEnabled(true);
                $galleryHasMedia->setMedia($media);
                $galleryHasMedias[] = $galleryHasMedia;
            }
            $count ++;

            $this->medias[] = [
                'temp_file' => $temp_file
            ];
        }

        if (!empty($galleryHasMedias)) {
            $gallery->setGalleryHasMedias($galleryHasMedias);

            $entity->setGallery($gallery);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function afterEntityPersist($entity)
    {
        foreach ($this->temp_files as $ref) {
            if (is_file($ref['temp_file'])) {
                unlink($ref['temp_file']);
            }
        }

        return false;
    }

    protected function stringsToFilter()
    {
        return [
            'jQuery'
        ];
    }

    protected function regexesToFilter()
    {
        return [
            '/^.{0,4}$/i'
        ];
    }

    /**
     * common
     */
    public function setCategoryManager($category_manager)
    {

        $this->categoryManager = $category_manager;
    }

    public function getCategory($name)
    {

        return $this->categoryManager->findOneBy(array('name' => $name));
    }

    public function slug($string)
    {
        return strtolower(trim(preg_replace('~[^0-9a-z]+~i', '-', preg_replace('~&([a-z]{1,2})(acute|cedil|circ|grave|lig|orn|ring|slash|th|tilde|uml);~i', '$1', htmlentities($string, ENT_QUOTES, 'UTF-8'))), ' '));
    }
    /*
     * Media functions
     */

    protected function matchMedias(array $medias)
    {

        $result = [];
        foreach ($medias as $url) {

            if ($youtube = $this->matchYoutubeVideo($url)) {
                //YOUTUBE
                $result[] = [
                    'url' => $url,
                    'id' => $youtube,
                    'provider' => 'sonata.media.provider.youtube',
                ];
            } else if ($sapo = $this->matchSapoVideo($url)) {
                //SAPO
                $result[] = [
                    'url' => $url,
                    'id' => $sapo,
                    'provider' => 'sonata.media.provider.sapo',
                ];
            } else if ($dailymotion = $this->matchDailymotionVideo($url)) {
                //DAILYMOTION
                $result[] = [
                    'url' => $url,
                    'id' => $dailymotion,
                    'provider' => 'sonata.media.provider.dailymotion',
                ];
            } else if ($vimeo = $this->matchVimeoVideo($url)) {
                //VIMEO
                $result[] = [
                    'url' => $url,
                    'id' => $vimeo,
                    'provider' => 'sonata.media.provider.vimeo',
                ];
            } else if ($playwire = $this->matchPlaywireVideo($url)) {
                //PLAYWIRE
                $result[] = [
                    'url' => $url,
                    'id' => $playwire,
                    'provider' => 'sonata.media.provider.playwire',
                ];
            } else if ($image = $this->matchImageMedia($url)) {

                //IMAGE
                $result[] = [
                    'url' => $image,
                    'provider' => 'sonata.media.provider.image',
                ];
            }
        }


        return $result;
    }

    protected function matchYoutubeVideo($url)
    {
        if (preg_match('/youtube\.com\/watch\?v=([^\&\?\/]+)/', $url, $id)) {
            $values = $id[1];
        } else if (preg_match('/youtube\.com\/embed\/([^\&\?\/]+)/', $url, $id)) {
            $values = $id[1];
        } else if (preg_match('/youtube\.com\/v\/([^\&\?\/]+)/', $url, $id)) {
            $values = $id[1];
        } else if (preg_match('/youtu\.be\/([^\&\?\/]+)/', $url, $id)) {
            $values = $id[1];
        } else if (preg_match('/youtube\.com\/verify_age\?next_url=\/watch%3Fv%3D([^\&\?\/]+)/', $url, $id)) {
            $values = $id[1];
        } else {
            // not an youtube video
            return false;
        }
        return $values;
    }

    protected function matchSapoVideo($url)
    {
        if (preg_match('/videos\.sapo\.pt\/([A-Za-z0-9]+)\/mov\//', $url, $id)) {
            $values = $id[1];
        } else {
            // not an sapo video
            return false;
        }

        return $values;
    }

    protected function matchDailymotionVideo($url)
    {
        if (preg_match('/dailymotion\.com\/embed\/video\/([^\&\?\/]+)/', $url, $id)) {
            $values = $id[1];
        } else {
            // not an dailymotion video
            return false;
        }

        return $values;
    }

    protected function matchVimeoVideo($url)
    {
        if (preg_match('/https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/', $url, $id)) {
            $values = $id[3];
        } else {
            // not an vimeo video
            return false;
        }

        return $values;
    }

    protected function matchPlaywireVideo($url)
    {
        ////config.playwire.com/1000748/videos/v2/3959845/zeus.json
        if (preg_match('/(?:config\.|player\.)?playwire.com\/(\d+)\/videos\/v2\/(\d+)\//', $url, $id)) {
            return $url;
        }
        return false;
    }

    protected function matchImageMedia($url)
    {
        if (preg_match('/\.(jpe?g|png|gif|bmp)$/i', $url)) {
            if (FALSE === strpos($url, $this->getHost())) {
                $url = rtrim($this->getHost(), '/') . $url;
            }

            return $url;
        }
        // not an image
        return false;
    }
}
