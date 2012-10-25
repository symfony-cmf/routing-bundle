# Symfony CMF Routing Extra Bundle [![Build Status](https://secure.travis-ci.org/symfony-cmf/RoutingExtraBundle.png)](http://travis-ci.org/symfony-cmf/RoutingExtraBundle)

This bundle enables the [CMF Routing component](https://github.com/symfony-cmf/Routing)
as Symfony2 bundle. It provides route documents for Doctrine PHPCR-ODM and a
controller for redirection routes.

The *chain router* is meant to replace the default Symfonys Router. All it does
is collect a prioritized list of routers and try to match requests and generate
urls with all of them. One of the routers in that chain can of course be the
default router so you can still use the standard way for some of your routes.

Additionally, this bundle delivers useful router implementations. Currently,
there is the *DynamicRouter* that routes based on a implemented repository that
provide Symfony2 Route objects. The repository can be implemented using a
database, for example with Doctrine PHPCR-ODM or Doctrine ORM. The bundle
provides a default implementation for Doctrine PHPCR-ODM.

The DynamicRouter service is only made available when explicitly enabled in the
application configuration.

See the [official documentation](http://symfony-cmf.readthedocs.org/en/latest/bundles/routing-extra.html)

## Installation

Add a requirement for ```symfony-cmf/routing-extra-bundle``` to your
composer.json and instantiate the bundle in your AppKernel.php

    new Symfony\Cmf\Bundle\RoutingExtraBundle\SymfonyCmfRoutingExtraBundle()

If you just want to use the chain router, this is enough.
For the DynamicRouter you need something to build a repository.
This bundle provides classes for Doctrine PHPCR ODM.

## Authors

* Filippo De Santis (p16)
* Henrik Bjornskov (henrikbjorn)
* Claudio Beatrice (omissis)
* Lukas Kahwe Smith (lsmith77)
* David Buchmann (dbu)
* Uwe Jäger (uwej711)
* [And others](https://github.com/symfony-cmf/RoutingExtraBundle/contributors)
