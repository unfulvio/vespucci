#Vespucci

A mapping and geo spatial framework for WordPress.

---

**WARNING:** this is an experimental, work-in-progress, WordPress plugin. In its current status, it's broken. **Do NOT use in production**. Read this document for details first.

---


###Introduction


WordPress has really nice and robust frameworks for ecommerce, social networking, meta data organization, events management, forms management, and many more. For each of these categories, in fact, there's more than one solution. Why can't we have one for geolocation and maps? There are many valid mapping plugins out there, but, regardless of their quality (some are rather good) most of them are one-man-band operations, thus they are not always well maintained, they were not planned as frameworks or meant to be extensible or modular, or don't allow much customization as most of them are meant for end users/bloggers, they are not built as a tool for other developers.  

This plugin is a very humble attempt to build a better framework to manage things like geo spatial coordinates, geolocation, location queries, locations to WordPress objects relationships, custom maps, all within WordPress, combined with Leaflet.js and external map tile provides (Google Maps, Mapbox, OpenStreetMaps, etc.).

At the core, it should allow users to store point coordinates and attach these to existing WordPress objects (namely posts, taxonomy terms, users, comments, or even custom objects in future), perform geo spatial queries and output results on customizable maps. It aims to be modular and extensible, to make it play nicely with other plugins or external services and let people build extensions or addons for it.

## Current status

In it's current version, consider this plugin an alpha or a minimum viable product, not meant for production, but only for demonstration purposes and idea pitching.  

####Database structure

What the plugin does, upon activation, is creating 3 tables (assuming `wp_` as standard WordPress table prefix):

* `wp_locations`
* `wp_location_relationships`
* `wp_locationmeta`

This should be intuitive for those familiar with WordPress database structure. `wp_locations` stores an object that describes a "location", represented by point coordinates and columns with human readable address parts. Each location can be attached to a WordPress object; this relationship is registered in `wp_location_relationships`. Finally in `wp_locationmeta` we have metadata for each location. The plugin has hooks (currently untested) to register more metadata to add to each location. 

####Attach locations

Users can decide which WordPress objects should have a location attached. This is set in plugin's settings page called `Objects`. In there, you'll find a list of WordPress objects. By ticking each object (listed by post types, by user roles, by taxonomies, all comments). Currently, only post types are supported (with the exception of media attachments) and saving options, the plugin will add a metabox to each object type where you will be able to add location data. The data won't be saved to the WordPress object (e.g. to post metadata) but to the specific tables above. This gives room to better data structure and ideally faster queries.

####Other options

In the plugin settings pages you also have the possibility to save some defaults. Some of these will set default values to the location box. Some other are meant to be helpful more widely. The plugin does not come yet with frontend functions and things like markers are just saved to database and that's it for now.

####Inspiration

This plugin was first drafted using Tom Mc Farlin's [WordPress Plugin Boilerplate](https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate). It was inspired by [GeoMashup](https://github.com/cyberhobo/wp-geo-mashup), however also by [WooCommerce](https://github.com/woothemes/woocommerce) and [Advanced Custom Fields](https://github.com/elliotcondon/acf).  
