#Vespucci

A mapping and geo spatial framework for WordPress.

---

**WARNING:** this is an experimental, work-in-progress, WordPress plugin. In its current status, it's broken. **Do NOT use in production**. Read this document for details first.

---


###Pitch


WordPress has really nice and robust frameworks for ecommerce, social networking, meta data organization, events management, forms management, and many more. For each of these categories, in fact, there's more than one solution. Why can't we have one for locations and maps? By that, it doesn't mean a plugin that simply stores coordinates in postmeta, adds a shortcode and publishes a Google Map in your post. Nor a bloated plugin that floods your dashboard with an astounding amount of options, the choice of 3000 markers in the attempt of covering every conceivable use case. But neither a plugin that doesn't follow best practices or doesn't adhere with WordPress standards (or sane coding standards or best contemporary practices).

Think instead of something well structured and solidly backed, say like WooCommerce, with a core part that absolves one essential task; in the case of a mapping plugin that task would be creating "location" objects to attach to WordPress common objects (post types, users, taxonomy terms, comments...) and describe these "locations" in database tables in a way that makes sense (index coordinates, allow for queries by distance, address, relationships...). But then, also provide room to extend this basic functionality with addons that are meant to cover each user specific case without bloating the whole plugin or overfeature a single plugin. For example, what if the user/developer wants to output maps using Mapbox instead of Google Maps? What if he/she wants to use different markers? Or wants to attach more metadata to a location, like a path, an itinerary, calculate the price of a product shipping by user distance? Sure you can do these things by coding them yourself, but there are many plugins out there used by developer to simplify their work, think of Gravity Forms or Advanced Custom Fields.

This plugin is a humble attempt to illustrate this concept. In it's current status, consider it as an alpha or a minimum viable product (MVP), not meant for production, but only for demonstration purposes and idea pitching. If you're curious, have also a look under the hood by looking at database chganges or code, after installation.

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

####Example ideas / uses / future development

The idea is NOT let the core plugin do everything, but build extensions to fill different purposes. A framework is intended to be modular, it's easier to maintain, allows for greater possibilities. The base function should be restricted to save coordinates for WordPress objects and some simple metadata. The rest is handled by extensions.  

* Generate frontend maps with anything: use leafletjs and Google Maps, Mapbox, OpenStreetMaps, Bing and other providers...
* Query posts by location, distance
* Geolocate current user or registered users (gives room for making conditional actions or delivering conditional content)
* Find posts or other WordPress objects nearby a user current position or registered position 
* Mention locations in a blog post and plot them on a map
* Create a travel diary and pin posts to a map
* Mashup WordPress geo-data with external services/third party apps
* BuddyPress integration
* WooCommerce integration (distance between a user and a product or store location?)
* Integration with other plugins
* Attach any type of metadata to locations, extend the location meta box and maybe save multiple points, itineraries, paths, drive directions...

