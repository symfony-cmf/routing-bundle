# Upgrade from 1.4 to 2.0

## Route Model

 * Removed `getAddFormatPattern()`/`setAddFormatPattern()` from the model
   `Route` and `getAddTrailingSlash()`/`setAddTrailingSlash()` from the PHPCR
   `Route`. Use `getOption()`/`setOption()` with `'add_format_pattern'` or
   `'add_trailing_slash'` instead.

   **Before**
   ```php
   $route->setAddFormatPattern(true);
   $route->setAddTrailingSlash(true);

   if ($route->getAddFormatPattern() || $route->getAddTrailingSlash()) {
       // ...
   }
   ```

   **After**
   ```php
   $route->setOption('add_format_pattern', true);
   $route->setOption('add_trailing_slash', true);

   if ($route->getOption('add_format_pattern') || $route->getOption('add_trailing_slash')) {
       // ...
   }
   ```

 * Removed `getParent()`/`setParent()` from PHPCR `Route` and `RedirectRoute`.
   Use `getParentDocument()`/`setParentDocument()` instead.

   **Before**
   ```php
   $route = new Route();
   $route->setParent($routeRoot);
   ```

   **After**
   ```php
   $route = new Route();
   $route->setParentDocument($routeRoot);
   ```

## Configuration

 * Removed the `route_basepath` setting, use `route_basepaths` instead.

   **Before**
   ```yaml
   cmf_routing:
       # ...
       dynamic:
           persistence:
               phpcr:
                   route_basepath: '/cms/routes'
   ```
   ```xml
   <config xmlns="http://cmf.symfony.com/schema/dic/routing">
       <dynamic>
           <persistence>
               <phpcr route-basepath="/cms/routes" />
           </persistence>
       </dynamic>
   </config>
   ```

   **After**
   ```yaml
   cmf_routing:
       # ...
       dynamic:
           persistence:
               phpcr:
                   route_basepaths: ['/cms/routes']
   ```
   ```xml
   <config xmlns="http://cmf.symfony.com/schema/dic/routing">
       <dynamic>
           <persistence>
               <phpcr>
                   <route-basepath>/cms/routes</route-basepath>
               </phpcr>
           </persistence>
       </dynamic>
   </config>
   ```
