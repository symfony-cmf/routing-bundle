<?php

namespace Symfony\Cmf\Bundle\RoutingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Cmf\Component\Routing\RedirectRouteInterface;

/**
 * ExternalRoute
 * @ORM\Entity(repositoryClass="Raindrop\RoutingBundle\Entity\ExternalRouteRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="external_routes")
 */
class ExternalRoute implements RedirectRouteInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $uri;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected $permanent;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set uri
     *
     * @param string $uri
     * @return ExternalRoute
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * May come handy
     * @return type
     */
    public function getUrl() {
        return $this->getUri();
    }

    /**
     * Set permanent
     *
     * @param boolean $permanent
     * @return ExternalRoute
     */
    public function setPermanent($permanent = true)
    {
        $this->permanent = $permanent;

        return $this;
    }

    /**
     * Get permanent
     *
     * @return boolean
     */
    public function getPermanent()
    {
        return $this->permanent;
    }

    public function isPermanent() {
        return $this->getPermanent();
    }

    /**
     * @TODO
     */
    public function getParameters() {
        return null;
    }

    /**
     * @TODO
     */
    public function getRouteTarget() {
        throw new LogicException('There is no route target for an external route, ask for uri instead');
    }

    /**
     * @TODO
     */
    public function getRouteName() {
        throw new LogicException('There is no name for an external route, ask for id or uri');
    }

    /**
     * The external route points to self ??
     *
     * @return \Raindrop\RoutingBundle\Entity\ExternalRoute
     */
    public function getRouteContent() {
        return $this;
    }

    public function getRouteKey() {
        return null;
    }
}
