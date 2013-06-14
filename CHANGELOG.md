Changelog
=========

* **2013-06-15**: [Model] Separated database agnostic, doctrine generic and
  PHPCR-ODM specific code to prepare for Doctrine ORM support. Deprecated
  RoutingBundle\Document\Route and RedirectRoute in favor of
  RoutingBundle\Doctrine\Phpcr\Route resp. RedirectRoute and also moved the
  PHPCR-ODM specific listeners and repository implementations to Doctrine\Phpcr

1.1.0-beta1
-----------

* **2013-05-28**: [Bundle] Only include Doctrine PHPCR compiler pass if PHPCR-ODM is present
* **2013-05-25**: [Bundle] Drop symfony_ from symfony_cmf prefix
* **2013-05-24**: [Document] ContentRepository now requires ManagerRegistry in the constructor and provides `setManagerName()` in the same way as RouteProvider
* **2013-05-24**: [Document] RouteProvider now requires ManagerRegistry in the constructor (the `cmf_routing.default_route_provider` does this for you)
  * To use a different document manager from the registry, call `setManagerName()` on the RouteProvider

