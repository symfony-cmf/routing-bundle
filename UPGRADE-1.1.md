UPGRADE FROM 1.0 TO 1.1
=======================

Deprecated `RoutingBundle\Document\Route` and `RedirectRoute` in favor of
`RoutingBundle\Doctrine\Phpcr\Route` resp. `RedirectRoute`.

PHPCR specific configurations moved into

    dynamic:
        phpcr_provider:
            enabled: true
            # the rest is optional, the defaults are
            route_basepath: ~
            manager_name: ~
            content_basepath: ~
            use_sonata_admin: auto

You need to at least set `phpcr_provider.enabled: true` to have the PHPCR provider loaded.

Dropped redundant unused `routing_repositoryroot` configuration.

Deprecated the setRouteContent and getRouteContent methods. Use setContent and
getContent instead.