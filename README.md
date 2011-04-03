WP MVC
==================================================
An MVC framework for WordPress

Overview
--------

WP MVC is a WordPress plugin that allows developers to use the methodologies of an MVC framework inside of WordPress. Since WordPress already provides a large amount of functionality for the data types that it supports out of the box (users, posts, pages, comments, categories, tags, and links), the primary focus of WP MVC is on other data types. WordPress supports custom post types natively, of course, but setting up custom post types and all of the necessary related functionality (public views, administrative management, associations, etc) is typically more time-consuming than doing the equivalent work in an MVC framework. The resulting code and database structure is significantly less graceful than the MVC equivalent, too.

WP MVC fills this gap. The basic idea is that you create an app/ directory that contains a file structure similar to other MVC frameworks (controllers/, helpers/, models/, views/, etc) and set up models, views, and controllers just as you would in other frameworks. WP MVC runs this code in the context of WordPress (i.e. you can still use all of WordPress's functionality inside of app/). Since WordPress already provides an administrative system, admin actions and views in app/ are run in that context, with WP MVC adding all of the necessary WordPress actions and filters to make this possible without the developer needing to lift a finger. An [Administration Menu](http://codex.wordpress.org/Administration_Menus) is automatically created for each model, but it can be customized or omitted.

Getting Started
---------------

If you've worked with MVC frameworks, most of WP MVC should already be familiar. The quickest introduction is probably the example application that's located in examples/. To set it up, simply copy the app/ directory into the root of this plugin's directory (so that it's at plugins/wp_mvc/app/) and run its SQL script to set up the tables and some example data. After doing so, there will be administrative menus for each model in WordPress, and you'll be able to browse to URLs like /events/, /events/1/, /venues/, etc to see the public-facing views.

Other Notes
-----------

This framework is still in development. Most of the functionality that's available is used in the example application, so if there's functionality that you'd like to use that isn't implemented in there, it may not exist yet. However, if it's something that is widely useful, I'd certainly be willing to implement it myself or to accept any well-written code that implements it. Please feel free to contact me through GitHub for any such requests.