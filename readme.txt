=== WP MVC ===
Contributors: tombenner, robertpeake
Tags: mvc, framework, model, view, controller, development, plugin
Requires at least: 3.0
Tested up to: 5.3
Stable tag: 1.3.20

WP MVC is a full-fledged MVC framework, similar to CakePHP and Rails, that developers can use inside of WordPress.

== Description ==

WP MVC is a WordPress plugin that allows developers to use a MVC framework to create plugins. It is a full-fledged framework with architecture that's similar to that of CakePHP or Ruby on Rails. Developers can consequently use it to rapidly build sites that take advantage of both WordPress's large native functionality and all of the many advantages of an MVC framework. 

WordPress supports a number of specific content types natively, but setting up custom post types and all of the necessary related functionality (public views, administrative management, associations, etc) is typically more time-consuming than doing the equivalent work in an MVC framework. The resulting code and database structure is significantly less graceful than the MVC equivalent, too.

WP MVC fills this gap. The basic idea is that you create an app/ directory that contains a file structure similar to other MVC frameworks (controllers/, helpers/, models/, views/, etc) and set up models, views, and controllers just as you would in other frameworks. WP MVC runs this code in the context of WordPress (i.e. you can still use all of WordPress's functionality inside of app/). Since WordPress already provides an administrative system, admin actions and views in app/ are run in that context, with WP MVC adding all of the necessary WordPress actions and filters to make this possible without the developer needing to lift a finger. An [Administration Menu](http://codex.wordpress.org/Administration_Menus) is automatically created for each model, but it can be customized or omitted.

For more extensive documentation, and to see what WP MVC is capable of, please visit [wpmvc.org](http://wpmvc.org).

If you'd like to grab development releases, see what new features are being added, or browse the source code please visit the [GitHub repo](http://github.com/tombenner/wp-mvc).

== Installation ==

1. Put `wp-mvc` into the `wp-content/plugins` directory
1. Activate the plugin in the "Plugins" menu in WordPress
1. Make sure that [Pretty Permalinks](http://codex.wordpress.org/Introduction_to_Blogging#Pretty_Permalinks) are enabled and working
1. Either set up one of the example plugins to see how WP MVC works or start creating a plugin using the code generation utility `wpmvc`:

#### Setting up one of the example WP MVC-based plugins:

1. Copy its directory (e.g. `wp-content/plugins/wp-mvc/examples/events-calendar-example`) into the `wp-content/plugins` directory (e.g. `wp-content/plugins/events-calendar-example`)
1. Activate the plugin in the "Plugins" menu in WordPress

After doing so, you should see administrative menus for each model in WordPress, and you'll be able to browse to URLs like `/events/`, `/events/1/`, `/venues/`, etc to see the public-facing views.

#### Creating a WP MVC-based plugin

It only takes four simple steps to create a basic WP MVC-based plugin:

1. Create the initial plugin code using a single command (WP MVC provides a code generation utility)
1. Write the SQL to create any tables that the plugin uses
1. Create the initial code for the models, views, and controllers using a single command for each resource
1. Modify the generated models, views, and controllers to customize the app

For a simple example tutorial on this, please see the [tutorial on wpmvc.org](http://wpmvc.org/documentation/tutorial/).

== Frequently Asked Questions ==

= I am getting a 404 when I add a new route =

You need to go to Settings > Permalinks and click "save". This <a href="https://codex.wordpress.org/Function_Reference/flush_rewrite_rules">flushes the WordPress rewrite rules</a>. For performance reasons, the rewrite rules are only flushed either when the plugin is activated or when the Permalinks are saved. So, if you are developing with the plugin activated and adding controller routes as you go, you need to use this approach to flush the rewrite rules and use your new URL endpoints.

= What relation does this have to other MVC frameworks? =

WP MVC is a full-fledged MVC framework, but behind the scenes it uses existing WordPress functionality to lessen its footprint and better interface with the parent WordPress application. The developer will not need to know about much of this, though, and may merely treat it as another MVC framework. It draws on concepts and workflows from other MVC frameworks; Rails and CakePHP are the biggest influences, and you may see some of their naming conventions being used.

= Is feature X available? =

If there's functionality that you'd like to use that isn't implemented in the example plugins or mentioned on [wpmvc.org](http://wpmvc.org), it may not exist yet. However, if it's something that is widely useful, I'd certainly be willing to implement it myself or to accept any well-written code that implements it. Please feel free to either add a topic in the WordPress forum or contact me through GitHub for any such requests:

* [WordPress Forum](http://wordpress.org/tags/wp-mvc?forum_id=10)
* [GitHub](http://github.com/tombenner)

== Screenshots ==

1. If you've worked with MVC frameworks before, the file structure for WP MVC will look refreshingly familiar.
2. Administration Menus are added automatically for each model, but they can be customized or omitted.
3. An example of the default "admin/index" view, which includes search functionality and pagination by default and can be customized.
4. An example of the default "admin/add" view. See the next screenshot for the code that creates it.
5. The code of the "admin/add" view in the previous screenshot. Forms can be easily created using the form helper, which includes an `input()` method that automatically determines the data type of the field and shows an appropriate input tag. Methods for most types of inputs (textareas, hidden inputs, select tags, checkboxes, etc) are also available, as are association-related input methods like `belongs_to_dropdown()` and `has_many_dropdown()`.

== Changelog ==

= 1.3.20 -

 * Composer cleanup
 * New HTML5 input fields (time, date, url, email)

= 1.3.19 =
 * Tested with 5.2
 * Minor admin html/code fixes

= 1.3.18 =
 * Tested with 5.1
 
= 1.3.17 =
 * Tested with 5.0

= 1.3.16 =
 * Replace create_function (deprecated in php 7.2.0) with lambda function
 * Improve ability to override views from template files

= 1.3.15 =

 * Cache describe table queries
 * Minor bugfixes

= 1.3.14
 
 * Define join type (left, right, inner) using the extra_on parameter in find()
 * Added 'CustomRoutePrefix' parameter in MvcConfiguration to prepend to URLs
 * Allow child controllers to override magic methods via __call()
 * New method to validate all fields at once and return errors in array

= 1.3.13 =

 * Complete the job to only start session in admin

= 1.3.12 =

 * Fix null type comparison issue when updating data
 * Only start session in admin
 * Fixed select tag to be able to accept an array of values

= 1.3.11 =

 * Ability to specify layouts in routes using `'layout' => 'whatever'`
 * Fix for textarea inputs in form helper
 * Added headers not sent check before setting cookies

= 1.3.10 =

 * php7 compatibility improvements

= 1.3.9 =
 * Added multi-language support

= 1.3.8 =
 * Added required input attribute type
 * Slightly better error messages for magic methods 

= 1.3.7 =
 * Better model validation in admin
 * Modernisation of classes (public/private, constructor)
 * New input type:
   * File input type with enctype attribute
   * Editor field input type
   * Select from model input type
 * Form submit button suppression
 * Optional admin table actions
 * Support for TRUE, FALSE and NULL comparitors in queries
 * Use of umeta_id in user meta model

= 1.3.6 =

 * Added required attribute for settings field
 * Resolved errors with static methods on input helpers 
 * Decorated tables with striped rows by default
 * Added experimental support for wp_editor input type
 
= 1.3.5 =
 * Allow the form helper to change enctype
 * Added HTML 5 number input type
 * Allow blank value for text inputs
 * Correct total count when grouping in query
 * Preserve table aliases in nested where clauses
 * Apply routes on plugin activation/deactivation (flush rewrite rules)
 * Allow checking for NULL conditions in queries
 * Allow IS and NOW() functions in queries
 * Validate model on creation

= 1.3.4 =
 * MvcDispatcher::dispatch() returns a value from controller methods to allow incorpation into widgets
 * Allow custom RouteParams (also allow users to not define any route params without a warning)
 * Allow multiple joins on a single table in has-many relations
 * Fixes to the has-many drop-down menu for remote objects

= 1.3.3. =
 * Resolve conflict with other plugins when session already begun

= 1.3.2 =
 * Minor bugfix with undefined var in method-not-found error for models

= 1.3.1 =
 * Fixed bug with event calendar example giving error on plugin activation
 * Added support for displaying custom menu icons (change the model $wp_post['post_type']['args']['menu_icon'] to a valid dashicon class name: https://developer.wordpress.org/resource/dashicons/#admin-site )

= 1.3 =

 * Added support for high-concurrency, high-traffic websites by refactoring rewrite rule initialization
 * Restructured classes to perform silently with WP_DEBUG set to true

= 1.2 =

* Model objects now have magic properties for accessing their associations (e.g. $event->venue, $event->speakers)
* Added model classes for most of the native WP tables (e.g. MvcPost, MvcUser), which can be used in the MVC context (e.g. as associations)
* Support for the automatic creation/updating of a post for each object of a model, so that objects can be commented on, added in menus, etc
* Support for easily creating admin settings pages through MvcSettings
* Associations can be dependent (e.g. if List has_many ListItems, when List is deleted, its ListItems can be automatically deleted)
* Moved configuration of admin menus from model to MvcConfiguration
* Moved configuration of admin_columns, admin_searchable_fields, and admin_search_joins from the model to the controller
* The 'controller' argument is no longer necessary for MvcRouter URL methods if 'object' is given
* Added a number of filters (e.g. MvcController::before and after, MvcModel::after_create(), 'mvc_before_public_url')
* Added 'group' clause to model select queries
* Added methods for aggregate select queries (e.g. $model->count(), max(), min(), sum(), average())
* Added MvcFormTagsHelper for creating inputs outside of object-related forms
* Let MvcModel::create() and save() accept objects
* Let MvcModel::to_url() optionally accept a second argument ($options)
* Allowed for a custom 'parent_slug' value in an admin menu page config

= 1.1.5 =
* Support for generating, destroying, and registering widgets
* Added HelpShell
* Allowed for a custom PHP executable to be set in the environment variable $WPMVC_PHP
* Allowed for the path to WordPress to be set in the environment variable $WPMVC_WORDPRESS_PATH
