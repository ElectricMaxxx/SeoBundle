<?php

namespace Symfony\Cmf\Bundle\SeoBundle\Doctrine\Phpcr;

use Symfony\Cmf\Bundle\CoreBundle\Translatable\TranslatableInterface;
use Symfony\Cmf\Bundle\SeoBundle\Extractor\SeoDescriptionInterface;
use Symfony\Cmf\Bundle\SeoBundle\Extractor\SeoOriginalUrlInterface;
use Symfony\Cmf\Bundle\SeoBundle\Extractor\SeoTitleInterface;
use Symfony\Cmf\Bundle\SeoBundle\Model\SeoAwareInterface;
use Symfony\Cmf\Bundle\SeoBundle\Model\SeoMetadata;
use Symfony\Cmf\Component\Routing\RouteReferrersInterface;
use Symfony\Component\Routing\Route;

/**
 * The bundle's own content class which supports the SeoAwareInterface.
 *
 * This interface is responsible for serving the SeoMeta or been recognised
 * for an sonata admin extension.
 *
 *
 * @author Maximilian Berghoff <Maximilian.Berghoff@gmx.de>
 */
class SeoAwareContent implements
    SeoAwareInterface,
    RouteReferrersInterface,
    TranslatableInterface,
    SeoTitleInterface,
    SeoDescriptionInterface,
    SeoOriginalUrlInterface
{

    /**
     * Primary identifier, details depend on storage layer.
     */
    protected $id;

    protected $node;

    protected $parent;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $body;

    /**
     * @var SeoMetadata
     */
    protected $seoMetadata;

    /**
     * @var RouteObjectInterface[]
     */
    protected $routes;

    /**
     * Explicitly set the primary id, if the storage layer permits this.
     *
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Any content model can handle its seo properties. By implementing
     * this interface a model has to return its class for all the seo properties
     * @todo find a better documentation
     *
     * @return SeoMetadata
     */
    public function getSeoMetadata()
    {
        return $this->seoMetadata;
    }

    /**
     * @param SeoMetadata $seoMetadata
     */
    public function setSeoMetadata($seoMetadata)
    {
        $this->seoMetadata= $seoMetadata;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $node
     */
    public function setNode($node)
    {
        $this->node = $node;
    }

    /**
     * @return mixed
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @param mixed $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * need to convert the object into an array, which can be persisted in
     * with the phpcr
     * @todo write issue to persist objects same way like arrays
     */
    public function preFlush()
    {
        $this->seoMetadata = $this->seoMetadata->toArray();
    }

    /**
     * wil set the information back to the seoMetadataMetadata object to handle it easier
     */
    public function postLoad()
    {
        $persistedData = $this->seoMetadata;
        $this->seoMetadata = new SeoMetadata();
        foreach ($persistedData as $property => $value) {
            if (method_exists($this->seoMetadata, 'set' . ucfirst($property))) {
                $this->seoMetadata->{'set' . ucfirst($property)}($value);
            }
        }
    }

    /**
     * @param Route $route
     */
    public function addRoute($route)
    {
        $this->routes->add($route);
    }

    /**
     * @param Route $route
     */
    public function removeRoute($route)
    {
        $this->routes->removeElement($route);
    }

    /**
     * @return \Symfony\Component\Routing\Route[] Route instances that point to this content
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @return string|boolean The locale of this model or false if
     *                        translations are disabled in this project.
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string|boolean $locale The local for this model, or false if
     *                               translations are disabled in this project.
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * Provide a title of this page to be used in SEO context.
     *
     * @return string
     */
    public function getSeoTitle()
    {
        $seoTitle = $this->getSeoMetadata()->getTitle();
        return null === $seoTitle || '' === $seoTitle
                ? $this->getTitle()
                : $seoTitle;
    }

    /**
     * Provide a description of this page to be used in SEO context.
     *
     * @return string
     */
    public function getSeoDescription()
    {
        $seoDescription = $this->getSeoMetadata()->getMetaDescription();
        return null === $seoDescription || '' == $seoDescription
                ? substr($this->getBody(), 0, 200).' ...'
                : $seoDescription;
    }

    /**
     * The method returns the absolute url as a string to redirect to
     * or set to the canonical link.
     *
     * @return string
     */
    public function getSeoOriginalUrl()
    {
        $seoOriginalUrl = $this->getSeoMetadata()->getOriginalUrl();
        return null === $seoOriginalUrl || '' === $seoOriginalUrl ? '/home' : $seoOriginalUrl;
    }
}
