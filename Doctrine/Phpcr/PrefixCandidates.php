<?php
namespace Symfony\Cmf\Bundle\RoutingBundle\Doctrine\Phpcr;

use Doctrine\ODM\PHPCR\DocumentManager;
use Symfony\Component\HttpFoundation\Request;

/**
 * Prefix based strategy to get route candidates.
 */
class PrefixCandidates implements CandidatesInterface
{
    /**
     * Places in the PHPCR tree where routes are located.
     *
     * @var array
     */
    protected $idPrefixes = array();

    /**
     * @var DocumentManager
     */
    protected $dm;

    public function __construct(array $prefixes)
    {
        $this->setPrefixes($prefixes);
    }

    public function isCandidate($name)
    {
        foreach ($this->getPrefixes() as $prefix) {
            // $name is the route document path
            if ('' === $prefix || 0 === strpos($name, $prefix)) {
                return true;
            }
        }

        return false;
    }

    public function getQueryRestriction()
    {return false; //TODO why is this called?
        $prefixConstraints = array();
        foreach ($this->getPrefixes() as $prefix) {
            if ('' == $prefix) {
                $prefixConstraints = array();
                break;
            }
            $prefixConstraints[] = 'ISDESCENDANTNODE(' . $this->dm->quote($prefix) . ')';
        }

        if (!count($prefixConstraints)) {
            return false;
        }

        return implode(' OR ', $prefixConstraints);
    }

    /**
     * {@inheritDoc}
     */
    public function getCandidates(Request $request)
    {
        $candidates = array();
        foreach ($this->getPrefixes() as $prefix) {
            $candidates = array_merge($candidates, $this->getCandidatesFor($prefix, $request->getPathInfo()));
        }

        return $candidates;
    }

    /**
     * Get the id candidates for one prefix.
     *
     * @param string $url
     *
     * @return array PHPCR ids that could load routes that match $url and are
     *      child of $prefix.
     */
    protected function getCandidatesFor($prefix, $url)
    {
        $candidates = array();
        if ('/' !== $url) {
            // handle format extension, like .html or .json
            if (preg_match('/(.+)\.[a-z]+$/i', $url, $matches)) {
                $candidates[] = $prefix . $url;
                $url = $matches[1];
            }

            $part = $url;
            while (false !== ($pos = strrpos($part, '/'))) {
                $candidates[] = $prefix . $part;
                $part = substr($url, 0, $pos);
            }
        }

        $candidates[] = $prefix;

        return $candidates;
    }

    /**
     * @param $prefix
     */
    public function setPrefixes($prefixes)
    {
        $this->idPrefixes = $prefixes;
    }

    /**
     * Append a repository prefix to the possible prefixes.
     *
     * @param $prefix
     */
    public function addPrefix($prefix)
    {
        $this->idPrefixes[] = $prefix;
    }

    /**
     * Get all currently configured prefixes where to look for routes.
     *
     * @return array
     */
    public function getPrefixes()
    {
        return $this->idPrefixes;
    }
}
