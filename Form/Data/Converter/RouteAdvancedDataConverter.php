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

/**
 * @author Maximilian Berghoff <Maximilian.Berghoff@mayflower.de>
 */
class RouteAdvancedDataConverter implements Converter
{
    /**
     * {@inheritdoc}
     *
     * @param RouteAdvancedData $dto
     * @param Route $document
     *
     * @return Route
     */
    public function toDocument($dto, $document)
    {
        $document->setVariablePattern($dto->variablePattern);
        $document->setDefaults($dto->defaults);
        $document->setOptions($dto->options);

        return $document;
    }

    /**
     * {@inheritdoc}
     *
     * @param Route $document
     * @param RouteAdvancedData $dto
     *
     * @return RouteAdvancedData
     */
    public function toDataTransferObject($document, $dto)
    {
        $dto->variablePattern = $document->getVariablePattern();
        $dto->defaults = $document->getDefaults();
        $dto->options = $document->getOptions();

        return $dto;
    }
}
