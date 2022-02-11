# Upgrade from 2.x to 3.0

## Parameter and return type declarations

If you extend any of the classes in this repository, you will need to adjust
overwritten methods to specify the parameter and return types.

## Content Template Request Attribute

The content template request attribute is only at `template` and no longer also at `contentTemplate`.
Use the `DynamicRouter::CONTENT_TEMPLATE` constant when referencing to it in code.

## RouteDefaultsTemplatingValidator removed

Use the RouteDefaultsTwigValidator instead.
