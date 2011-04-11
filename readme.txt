=== WP MVC ===
Contributors: tombenner
Tags: mvc, framework, model, view, controller, development, plugin
Requires at least: 3.0
Tested up to: 3.1.1
Stable tag: trunk

WP MVC is a full-fledged MVC framework, similar to CakePHP and Rails, that developers can use inside of WordPress.

== Description ==

WP MVC is a WordPress plugin that allows developers to use an MVC framework inside of WordPress. Since WordPress already provides a large amount of functionality for the content types that it supports out of the box (users, posts, pages, comments, categories, tags, and links), the primary focus of WP MVC is on other content types. WordPress supports custom post types natively, of course, but setting up custom post types and all of the necessary related functionality (public views, administrative management, associations, etc) is typically more time-consuming than doing the equivalent work in an MVC framework. The resulting code and database structure is significantly less graceful than the MVC equivalent, too.

WP MVC fills this gap. The basic idea is that you create an app/ directory that contains a file structure similar to other MVC frameworks (controllers/, helpers/, models/, views/, etc) and set up models, views, and controllers just as you would in other frameworks. WP MVC runs this code in the context of WordPress (i.e. you can still use all of WordPress's functionality inside of app/). Since WordPress already provides an administrative system, admin actions and views in app/ are run in that context, with WP MVC adding all of the necessary WordPress actions and filters to make this possible without the developer needing to lift a finger. An [Administration Menu](http://codex.wordpress.org/Administration_Menus) is automatically created for each model, but it can be customized or omitted.

The most recent version will always be in the [GitHub repo](http://github.com/tombenner/wp-mvc).

== Installation ==

1. Upload `wp-mvc` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Either set up the example application to see how WP MVC works or start creating an application using the code generation utility `wpmvc`:

#### Setting up the example application:

1. Run the example application's SQL script (`wp-mvc/examples/events_calendar/create_tables_and_insert_data.sql`) to create its tables and insert some example data.

1. Copy the app/ directory (`wp-mvc/examples/events_calendar/app/`) into the root of this plugin's directory (so that it's at `wp-mvc/app/`). After doing so, there will be administrative menus for each model in WordPress, and you'll be able to browse to URLs like /events/, /events/1/, /venues/, etc to see the public-facing views.

#### Creating an application using the WP MVC console to generate initial code

1. Create the table in the database that will be used for a resource (e.g. for a resource named MyVenue, create a table named `my_venues`).

1. Make sure that `wpmvc` is executable

	`cd path/to/plugins/wp-mvc`
	
	`chmod +x wpmvc`

1. Create the initial code for a resource's model, view, and controllers.

	`./wpmvc generate scaffold MyVenue`

1. The generated code will be in `plugins/wp-mvc/app/` and assumes that a database column named `name` is present and will be used to represent the resource in views. (See the example application for examples of how to modify this.) There will now be an administrative menu (`My Venues`) for this resource in WordPress, and you'll be able to browse to URLs like /my_venues/ and /my_venues/1/ to see the public-facing views.

1. Flesh out the code or create code for more resources using the `generate scaffold` command as shown above.

== Frequently Asked Questions ==

= What relation does this have to other MVC frameworks? =

WP MVC is a full-fledged MVC framework, but behind the scenes it uses existing WordPress functionality to lessen its footprint and better interface with the parent WordPress application. The developer will not need to know about much of this, though, and may merely treat it as another MVC framework. It draws on concepts and workflows from other MVC frameworks; Rails and CakePHP are the biggest influences, and you may see some of their naming conventions being used.

= Is feature X available? =

This framework is still in development. Most of the functionality that's available is used in the example application, so if there's functionality that you'd like to use that isn't implemented in there, it may not exist yet. However, if it's something that is widely useful, I'd certainly be willing to implement it myself or to accept any well-written code that implements it. Please feel free to either add a topic in the WordPress forum or contact me through GitHub for any such requests:

* [WordPress Forum](http://wordpress.org/tags/wp-mvc?forum_id=10)
* [GitHub](http://github.com/tombenner/)

== Screenshots ==

1. If you've worked with MVC frameworks before, the file structure for WP MVC will look refreshingly familiar.