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
use Symfony\Cmf\Bundle\RoutingBundle\Form\Data\RouteGeneralData;

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class RouteGeneralDataConverter implements Converter
{
    /**
     * {@inheritdoc}
     *
     * @param RouteGeneralData $dto
     * @param Route $document
     *
     * @return Route
     */
    public function toDocument($dto, $document)
    {
        $document->setParentDocument($dto->parentDocument);
        $document->setName($dto->name);

        return $document;
    }

    /**
     * {@inheritdoc}
     *
     * @param Route $document
     * @param RouteGeneralData $dto
     *
     * @return RouteGeneralData
     */
    public function toDataTransferObject($document, $dto)
    {
        $dto->parentDocument = $document->getParentDocument();
        $dto->name = $document->getName();

        return $dto;
    }
}
