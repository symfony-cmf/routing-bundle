<?php

/*
 * This file is part of the Symfony CMF package.
 *
 * (c) Symfony CMF
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr;

/**
 * An interface for PHPCR route documents.
 *
 * We use the repository path as static part of the URL, but the documents
 * need to know what their base path is to remove that from the URL.
 */
interface PrefixInterface
{
    /**
     * Return the full path of this document in the repository.
     */
    public function getId(): string;

    /**
     * Set the path in the repository to the routing root document.
     */
    public function setPrefix(string $prefix): static;
}
