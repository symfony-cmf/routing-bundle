Changelog
=========

1.0.0-RC2
---------

* **2013-08-04**: [PHPCR-ODM] Fixed the configuration of the LocaleListener to
  make routes again automatically provide the _locale based on their repository
  path if the bundle is configured with locales.
* **2013-08-04**: [Bundle] Only build the compiler passes for ORM and PHPCR-ODM
  if all required repositories are present.

1.0.0-RC1
---------

* **2013-07-31**: [EventDispatcher] Added events to the dynamic router at the start of match and matchRequest
* **2013-07-29**: [DependencyInjection] restructured `phpcr_provider` config into `persistence` -> `phpcr` to match other Bundles
* **2013-07-28**: [DependencyInjection] added `enabled` flag to `phpcr_provider` config
* **2013-07-26**: [Model] Removed setRouteContent and getRouteContent, use setContent and getContent instead.
* **2013-07-19**: [Model] Separated database agnostic, doctrine generic and
  PHPCR-ODM specific code to prepare for Doctrine ORM support.
* **2013-07-17**: [FormType] Moved TermsFormType to CoreBundle and renamed it to CheckboxUrlLableFormType

1.1.0-beta2
-----------

* **2013-05-28**: [Bundle] Only include Doctrine PHPCR compiler pass if PHPCR-ODM is present
* **2013-05-25**: [Bundle] Drop symfony_ from symfony_cmf prefix
* **2013-05-24**: [Document] ContentRepository now requires ManagerRegistry in the constructor and provides `setManagerName()` in the same way as RouteProvider
* **2013-05-24**: [Document] RouteProvider now requires ManagerRegistry in the constructor (the `cmf_routing.default_route_provider` does this for you)
  * To use a different document manager from the registry, call `setManagerName()` on the RouteProvider
