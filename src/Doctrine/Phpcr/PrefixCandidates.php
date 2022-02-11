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

use Doctrine\ODM\PHPCR\DocumentManager;
use Doctrine\ODM\PHPCR\Query\Builder\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use PHPCR\Util\PathHelper;
use Symfony\Cmf\Component\Routing\Candidates\Candidates;
use Symfony\Component\HttpFoundation\Request;

/**
 * Prefix based strategy for storing routes in a tree with several prefixes.
 *
 * @author David Buchmann <mail@davidbu.ch>
 */
final class PrefixCandidates extends Candidates
{
    /**
     * Places in the PHPCR tree where routes are located.
     *
     * @var string[]
     */
    private array $idPrefixes = [];

    private ?string $managerName = null;

    private ?ManagerRegistry $doctrine;

    /**
     * @param string[]             $prefixes The prefixes to use. If one of them is
     *                                       an empty string, the whole repository
     *                                       is used for routing
     * @param string[]             $locales  Allowed locales
     * @param ManagerRegistry|null $doctrine Used when the URL matches one of the
     *                                       $locales. This must be the same
     *                                       document manager as the RouteProvider
     *                                       is using
     * @param int                  $limit    Limit to candidates generated per prefix
     */
    public function __construct(array $prefixes, array $locales = [], ManagerRegistry $doctrine = null, int $limit = 20)
    {
        parent::__construct($locales, $limit);
        $this->setPrefixes($prefixes);
        $this->doctrine = $doctrine;
    }

    /**
     * {@inheritdoc}
     *
     * A name is a candidate if it starts with one of the prefixes
     */
    public function isCandidate($name): bool
    {
        foreach ($this->getPrefixes() as $prefix) {
            // $name is the route document path
            if (($name === $prefix || str_starts_with($name, $prefix.'/'))
                && PathHelper::assertValidAbsolutePath($name, false, false)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param QueryBuilder $queryBuilder
     */
    public function restrictQuery($queryBuilder): void
    {
        $prefixes = $this->getPrefixes();
        if (\in_array('', $prefixes, true) || !\count($prefixes)) {
            return;
        }

        $where = $queryBuilder->andWhere()->orX();
        foreach ($prefixes as $prefix) {
            $where->descendant($prefix, $queryBuilder->getPrimaryAlias());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCandidates(Request $request): array
    {
        $candidates = [];
        $url = rawurldecode($request->getPathInfo());
        foreach ($this->getPrefixes() as $prefix) {
            $candidates = array_unique(array_merge($candidates, $this->getCandidatesFor($url, $prefix)));
        }

        $locale = $this->determineLocale($url);
        if ($locale) {
            $url = substr($url, \strlen($locale) + 1);
            foreach ($this->getPrefixes() as $prefix) {
                $candidates = array_unique(array_merge($candidates, $this->getCandidatesFor($url, $prefix)));
            }
        }

        // filter out things like double // or trailing / - this would trigger an exception on the document manager.
        foreach ($candidates as $key => $candidate) {
            if (!$this->isCandidateValid($candidate)) {
                unset($candidates[$key]);
            }
        }

        return $candidates;
    }

    /**
     * Set the prefixes handled by this strategy.
     *
     * @param string[] $prefixes List of prefixes, possibly including ''
     */
    public function setPrefixes(array $prefixes): void
    {
        $this->idPrefixes = $prefixes;
    }

    /**
     * Append a prefix to the allowed prefixes.
     */
    public function addPrefix(string $prefix): void
    {
        $this->idPrefixes[] = $prefix;
    }

    /**
     * Get all currently configured prefixes where to look for routes.
     *
     * @return string[] The prefixes
     */
    public function getPrefixes(): array
    {
        return $this->idPrefixes;
    }

    /**
     * Set the doctrine document manager name. Set to `null` to use the default manager.
     */
    public function setManagerName(?string $manager): void
    {
        $this->managerName = $manager;
    }

    private function isCandidateValid(string $candidate): bool
    {
        // Candidates cannot start or end with a space in Jackrabbit.
        if (str_starts_with($candidate, ' ') || str_ends_with($candidate, ' ')) {
            return false;
        }

        // Jackrabbit does not allow spaces before or after the path separator.
        if (str_contains($candidate, ' /') || str_contains($candidate, '/ ')) {
            return false;
        }

        if (!PathHelper::assertValidAbsolutePath($candidate, false, false)) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * The normal phpcr-odm locale listener "waits" until the routing completes
     * as the locale is usually defined inside the route. We need to set it
     * already in case the route document itself is translated.
     *
     * For example the CmfSimpleCmsBundle Page documents.
     */
    protected function determineLocale($url): bool|string
    {
        $locale = parent::determineLocale($url);
        if ($locale && $this->doctrine) {
            $this->getDocumentManager()->getLocaleChooserStrategy()->setLocale($locale);
        }

        return $locale;
    }

    private function getDocumentManager(): DocumentManager
    {
        return $this->doctrine->getManager($this->managerName);
    }
}
