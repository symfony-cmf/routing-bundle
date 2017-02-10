# Upgrade from 1.4 to 2.0

### SonataAdmin Support

 * The Admin extensions where moved into `symfony-cmf/sonata-admin-integration-bundle`.
   With the move, the admin extension service names also changed. If you are using one of the routing extensions,
   you need to adjust your configuration.
   
   Before:
   
   ```yaml
        # app/config/config.yml
     
        sonata_admin:
            extensions:
                cmf_routing.admin_extension.route_referrers:
                     implements:
                         - Symfony\Cmf\Component\Routing\RouteReferrersInterface
                cmf_core.admin_extension.publish_workflow.time_period:
                     implements:
                         - Symfony\Cmf\Component\Routing\RouteReferrersReadInterface
   ```

   After:
       
   ```yaml
        # app/config/config.yml
                
        sonata_admin:
            extensions:
                 cmf_sonata_admin_integration.routing.extension.route_referrers:
                     implements:
                         - Symfony\Cmf\Component\Routing\RouteReferrersInterface
                 cmf_sonata_admin_integration.routing.extension.frontend_link:
                     implements:
                         - Symfony\Cmf\Component\Routing\RouteReferrersReadInterface
   ```
   Admin service names also changed. If you are using the admin, you need to adjust your configuration,
   i.e. in the sonata dashboard:
   
   Before:
   
   ```yaml
        # app/config/config.yml
        sonata_admin:
            dashboard:
               groups:
                   content:
                       label: URLs
                       icon: '<i class="fa fa-file-text-o"></i>'
                       items:
                           - cmf_routing.route_admin
                           - cmf_routing.redirect_route_admin
   ```

   After:
       
   ```yaml
        # app/config/config.yml
        sonata_admin:
           dashboard:
               groups:
                   content:
                       label: URLs
                       icon: '<i class="fa fa-file-text-o"></i>'
                       items:
                           - cmf_sonata_admin_integration.routing.route_admin
                           - cmf_sonata_admin_integration.routing.redirect_route_admin
   ```

 * The settings `admin_basepath` and `content_basepath` are only relevant for
   the admin and thus have been moved as well.

   Before:

   ```yaml
   cmf_routing:
       # ...
       dynamic:
           persistence:
               phpcr:
                   admin_basepath: '/cms/routes'
                   content_basepath: '/cms/content'
   ```
   ```xml
   <config xmlns="http://cmf.symfony.com/schema/dic/routing">
       <dynamic>
           <persistence>
               <phpcr
                   admin-basepath="/cms/routes"
                   content-basepath="/cms/content"
                />
           </persistence>
       </dynamic>
   </config>
   ```

   After:

   ```yaml
   cmf_sonata_phpcr_admin_integration:
       # ...
       bundles:
           routing:
               basepath: '/cms/routes'
               content_basepath: '/cms/content'
   ```
   ```xml
   <config xmlns="http://cmf.symfony.com/schema/dic/sonata-phpcr-admin-integration">
       <bundles>
           <routing
               basepath="/cms/routes"
               content-basepath="/cms/content"
            />
       </bundles>
   </config>
   ```

### Route Model

 * Removed `getAddFormatPattern()`/`setAddFormatPattern()` from the model
   `Route` and `getAddTrailingSlash()`/`setAddTrailingSlash()` from the PHPCR
   `Route`. Use `getOption()`/`setOption()` with `'add_format_pattern'` or
   `'add_trailing_slash'` instead.

   Before:

   ```php
   $route->setAddFormatPattern(true);
   $route->setAddTrailingSlash(true);

   if ($route->getAddFormatPattern() || $route->getAddTrailingSlash()) {
       // ...
   }
   ```

   After:

   ```php
   $route->setOption('add_format_pattern', true);
   $route->setOption('add_trailing_slash', true);

   if ($route->getOption('add_format_pattern') || $route->getOption('add_trailing_slash')) {
       // ...
   }
   ```

 * Removed `getParent()`/`setParent()` from PHPCR `Route` and `RedirectRoute`.
   Use `getParentDocument()`/`setParentDocument()` instead.

   Before:

   ```php
   $route = new Route();
   $route->setParent($routeRoot);
   ```

   After:

   ```php
   $route = new Route();
   $route->setParentDocument($routeRoot);
   ```

### Configuration

 * Removed the `route_basepath` setting, use `route_basepaths` instead.

   Before:

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

   After:

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

### Route Template

 * The `contentTemplate` request attribute is deprecated in favor of
   `template`. The CmfContentBundle has been adjusted accordingly.

   Before:

   ```php
   public function documentAction($contentDocument, $contentTemplate)
   {
       // ...
   }
   ```

   After:

   ```php
   public function documentAction($contentDocument, $template)
   {
       // ...
   }
   ```
   
   The deprecated `contentTemplate` will be kept for backwards compatibility
   and will be removed in version 3.0.
