<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2014 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Symfony\Cmf\Bundle\RoutingBundle\Twig\Extension;

use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RoutingExtension extends \Twig_Extension
{
    /**
     * @var UrlGeneratorInterface
     */
    protected $generator;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var mixed
     */
    protected $routeNotFoundLogLevel;

    /**
     * @param UrlGeneratorInterface $generator 
     */
    public function __construct(UrlGeneratorInterface $generator, LoggerInterface $logger = null, $routeNotFoundLogLevel = 300)
    {
        $this->generator = $generator;
        $this->logger = $logger;
        $this->routeNotFoundLogLevel = $routeNotFoundLogLevel;
    }

    /**
     * {@inheritdoc}
     * TODO: Should Symfony\Bridge\Twig\Extension\RoutingExtension::isUrlGenerationSafe should be used/duplicated?
     */
    public function getFunctions()
    {
        $functions = array(
            new \Twig_SimpleFunction('cmf_document_path', array($this, 'getDocumentPath')),
            new \Twig_SimpleFunction('cmf_document_url', array($this, 'getDocumentUrl')),
        );

        return $functions;
    }

    public function getDocumentPath($id, array $parameters = array(), $relative = false)
    {
        try {
            return $this->generator->generate($id, $parameters, $relative ? UrlGeneratorInterface::RELATIVE_PATH : UrlGeneratorInterface::ABSOLUTE_PATH);
        } catch (RouteNotFoundException $e) {
            $this->handleRouteNotFound($id, $parameters, $e);
        }
    }

    public function getDocumentUrl($id, array $parameters = array(), $schemeRelative = false)
    {
        try {
            return $this->generator->generate($id, $parameters, $schemeRelative ? UrlGeneratorInterface::NETWORK_PATH : UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (RouteNotFoundException $e) {
            $this->handleRouteNotFound($id, $parameters, $e);
        }
    }

    /**
     * Handle a RouteNotFound exception
     * 
     * @param string $id The document id (/cms/routes/route1)
     * @param array $parameters Route parameters
     */
    protected function handleRouteNotFound($id, array $parameters, RouteNotFoundException $exception)
    {
        if (!$this->logger) {
            return false;
        }

        $this->logger->log(
            $this->routeNotFoundLogLevel,
            'Route Not Found: '.$id,
            array(
                'id' => $id,
                'parameters' => $parameters,
                'exception' => $exception->getMessage()
            )
        );
    }

    public function getName()
    {
        return 'cmf_routing';
    }
}
