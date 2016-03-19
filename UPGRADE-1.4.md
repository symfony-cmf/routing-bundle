UPGRADE FROM 1.3 TO 1.4
=======================

Configuration
-------------

 * Renamed the `cmf_routing.dynamic.persistence.phpcr.route_basepath` setting
   to `route_basepaths`.

   **Before**
   ```yaml
   cmf_routing:
       dynamic:
           persistence:
               phpcr:
                   route_basepath: /cms/custom
   ```

   ```xml
   <config xmlns="http://cmf.symfony.com/schema/dic/routing">
       <dynamic>
           <persistence>
               <phpcr route-basepath="/cms/custom" />
           </persistence>
       </dynamic>
   </config>
    ```

   **After**
   ```yaml
   cmf_routing:
       dynamic:
           persistence:
               phpcr:
                   route_basepaths: /cms/custom
   ```

   ```xml
   <config xmlns="http://cmf.symfony.com/schema/dic/routing">
       <dynamic>
           <persistence>
               <phpcr>
                   <route-basepath>/cms/custom</route-basepath>
               </phpcr>
           </persistence>
       </dynamic>
   </config>
    ```

DependencyInjection
-------------------

 * Renamed the `cmf_routing.dynamic.persistence.phpcr.route_basepath` parameter
   to `cmf_routing.dynamic.persistence.phpcr.route_basepaths` and changed its
   type to an array.
