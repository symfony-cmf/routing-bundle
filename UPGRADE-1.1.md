UPGRADE FROM 1.0 TO 1.1
=======================

Deprecated `RoutingBundle\Document\Route` and `RedirectRoute` in favor of
`RoutingBundle\Doctrine\Phpcr\Route` resp. `RedirectRoute`.

PHPCR specific configurations moved into

    dynamic:
        phpcr_provider:
            use_sonata_admin: auto
            manager_registry: ~
            manager_name: ~
            route_basepath: ~
            content_basepath: ~

You need to at least set `phpcr_provider: ~` to have the PHPCR provider loaded.

Dropped redundant unused `routing_repositoryroot` configuration.