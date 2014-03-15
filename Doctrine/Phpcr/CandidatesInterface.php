<?php
namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr;

use Symfony\Component\HttpFoundation\Request;

interface CandidatesInterface
{
    /**
     * @param Request $request
     *
     * @return array a list of PHPCR-ODM ids
     */
    function getCandidates(Request $request);

    /**
     * Determine if $name is a valid candidate, e.g. in getRouteByName.
     *
     * @param string $name
     *
     * @return boolean
     */
    function isCandidate($name);

    /**
     * Provide a best effort query restriction to limit a query to only find
     * routes that are supported.
     *
     * TODO: this would better use the query builder.
     *
     * @return string|boolean an sql2 query fragment to limit to valid candidates.
     */
    function getQueryRestriction();
}
