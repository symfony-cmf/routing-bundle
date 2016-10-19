<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) 2011-2016 Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Form\Data\Converter;

use Symfony\Cmf\Bundle\CoreBundle\Form\Data\Converter;
use Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr\Route;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\RouteAdvancedData;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\RouteData;
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\RouteGeneralData;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class RouteDataConverter implements Converter
{
    /**
     * @var RouteAdvancedDataConverter
     */
    private $routeAdvancedDataConverter;

    /**
     * @var RouteGeneralDataConverter
     */
    private $routeGeneralDataConverter;

    /**
     * @param Converter $converter
     */
    public function setRouteAdvancedDataConverter(Converter $converter)
    {
        $this->routeAdvancedDataConverter = $converter;
    }

    /**
     * @param Converter $converter
     */
    public function setRouteGeneralDataConverter(Converter $converter)
    {
        $this->routeGeneralDataConverter = $converter;
    }

    /**
     * {@inheritdoc}
     *
     * @param RouteData $dto
     * @param Route $document
     *
     * @return Route
     */
    public function toDocument($dto, $document)
    {
        $document = $this->routeAdvancedDataConverter->toDocument($dto->routeAdvanced, $document);
        $document = $this->routeGeneralDataConverter->toDocument($dto->routeGeneral, $document);

        return $document;
    }

    /**
     * {@inheritdoc}
     *
     * @param Route $document
     * @param RouteData $dto
     *
     * @return RouteData
     */
    public function toDataTransferObject($document, $dto)
    {
        $dto->routeAdvanced = $this->routeAdvancedDataConverter->toDataTransferObject($document, new  RouteAdvancedData());
        $dto->routeGeneral = $this->routeGeneralDataConverter->toDataTransferObject($document, new  RouteGeneralData());

        return $dto;
    }
}
