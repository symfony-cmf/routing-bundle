Changelog
=========

* **2013-07-19**: [Model] Separated database agnostic, doctrine generic and
  PHPCR-ODM specific code to prepare for Doctrine ORM support.
* **2013-07-17**: [FormType] Moved TermsFormType to CoreBundle and renamed it to CheckboxUrlLableFormType

1.1.0-beta2

* **2013-05-28**: [Bundle] Only include Doctrine PHPCR compiler pass if PHPCR-ODM is present
* **2013-05-25**: [Bundle] Drop symfony_ from symfony_cmf prefix
* **2013-05-24**: [Document] ContentRepository now requires ManagerRegistry in the constructor and provides `setManagerName()` in the same way as RouteProvider
* **2013-05-24**: [Document] RouteProvider now requires ManagerRegistry in the constructor (the `cmf_routing.default_route_provider` does this for you)
  * To use a different document manager from the registry, call `setManagerName()` on the RouteProvider

