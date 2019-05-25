<?php

/**
 *
 * Simple Example class for a multisite safe plugin
 *
 * @class EventsCalendarExampleLoader
 *
 * @author Nicolas Gosselin
 */
class EventsCalendarExampleLoader extends MvcPluginLoader {

  /**
   * @var float $db_version
   *
   * Version number to put in DB
   */
  private $db_version = 2.0;

  /**
   * @var array $tables
   *
   * Variable to store the tables to create
   */
  private $tables = array();

  public function init() {}

  public function activate($network_wide = FALSE) {
      global $wpdb;

      require_once ABSPATH.'wp-admin/includes/upgrade.php';

      // check if it is a network activation - if so, run the activation public function for each blog id
      if ($network_wide && function_exists('is_multisite') && is_multisite()) {

          // Get all blog ids and activate / create tables on each current
          // exisiting blog
          $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
          foreach ($blog_ids as $blog_id) {
            $this->activate_blog($blog_id);
          }
      }
      else
      {
        // single blog
        $this->activate_blog();
      }

      // This call needs to be made to activate this app within WP MVC
      $this->activate_app(__FILE__);

      return;
  }

  public function deactivate($network_wide = FALSE) {
    global $wpdb;

    require_once ABSPATH.'wp-admin/includes/upgrade.php';

    // This call needs to be made to deactivate this app within WP MVC
    $this->deactivate_app(__FILE__);

    // check if it is a network activation - if so, run the deactivation public function for each blog id
    if ($network_wide && function_exists('is_multisite') && is_multisite()) {
      // check if it is a network activation - if so, run the activation public function for each blog id
        if (!empty($network_wide) && $network_wide) {
            // Get all blog ids
            $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
            foreach ($blog_ids as $blog_id) {
              $this->deactivate_blog($blog_id);
            }
        }
    }
    else
    {
      // single blog
      $this->deactivate_blog();
    }

    return;
  }

  /**
   * activate_blog()
   *
   * Setup the required tables for the plugin for $blog_id.
   *
   * @param $blog_id - The id of the blog to work against
   *
   * @return void
   */
  public function activate_blog($blog_id = 1) {
    if ($blog_id == 1) {
      add_option('events_calendar_example_db_version', $this->db_version);
      $this->create_tables();
    }
    else
    {
      switch_to_blog($blog_id);
      add_option('events_calendar_example_db_version', $this->db_version);
      $this->create_tables();
      restore_current_blog();
    }
  }

  /**
   * deactivate_blog()
   *
   * Remove the tables used by the plugin for $blog_id.
   *
   * @param $blog_id - The id of the blog to work against
   *
   * @return void
   */
  public function deactivate_blog($blog_id = 1) {
    if ($blog_id == 1) {
      delete_option('events_calendar_example_db_version');
      $this->delete_tables();
    }
    else
    {
      switch_to_blog($blog_id);
      delete_option('events_calendar_example_db_version');
      $this->delete_tables();
      restore_current_blog();
    }
  }

  /**
   * create_tables()
   *
   * Create the required table for the plugin.
   *
   * @return void
   */
  private function create_tables() {
        global $wpdb;

        // this needs to occur at this level, and not in the
        // constructor/init since we are switching blogs for multisite
        $this->tables = array(
            'events' => $wpdb->prefix.'events',
            'events_speakers' => $wpdb->prefix.'events_speakers',
            'speakers' => $wpdb->prefix.'speakers',
            'venues' => $wpdb->prefix.'venues'
        );

        $sql = '
            CREATE TABLE '.$this->tables['events'].' (
              id int(11) NOT NULL auto_increment,
              venue_id int(9) default NULL,
              date date default NULL,
              time time default NULL,
              description text,
              is_public tinyint(1) NOT NULL default 0,
              PRIMARY KEY  (id),
              KEY venue_id (venue_id)
            )';
        dbDelta($sql);

        $sql = '
            CREATE TABLE '.$this->tables['events_speakers'].' (
              id int(7) NOT NULL auto_increment,
              event_id int(11) default NULL,
              speaker_id int(11) default NULL,
              PRIMARY KEY  (id),
              KEY event_id (event_id),
              KEY speaker_id (speaker_id)
            )';
        dbDelta($sql);

        $sql = '
            CREATE TABLE '.$this->tables['speakers'].' (
              id int(8) NOT NULL auto_increment,
              first_name varchar(255) default NULL,
              last_name varchar(255) default NULL,
              url varchar(255) default NULL,
              description text,
              post_id BIGINT(20),
              PRIMARY KEY  (id),
              KEY post_id (post_id)
            )';
        dbDelta($sql);

        $sql = '
            CREATE TABLE '.$this->tables['venues'].' (
              id int(11) NOT NULL auto_increment,
              name varchar(255) NOT NULL,
              sort_name varchar(255) NOT NULL,
              url varchar(255) default NULL,
              description text,
              address1 varchar(255) default NULL,
              address2 varchar(255) default NULL,
              city varchar(100) default NULL,
              state varchar(100) default NULL,
              zip varchar(20) default NULL,
              post_id BIGINT(20),
              PRIMARY KEY  (id),
              KEY post_id (post_id)
            )';
        dbDelta($sql);

        $this->insert_example_data();
    }

  /**
   * delete_tables()
   *
   * Delete the tables which are required by the plugin.
   *
   * @return void
   */
  private function delete_tables() {
      global $wpdb;

      // this needs to occur at this level, and not in the
      // constructor/init since we are switching blogs for multisite
      $this->tables = array(
          'events' => $wpdb->prefix.'events',
          'events_speakers' => $wpdb->prefix.'events_speakers',
          'speakers' => $wpdb->prefix.'speakers',
          'venues' => $wpdb->prefix.'venues'
      );

      $sql = 'DROP TABLE IF EXISTS ' . $this->tables['events'];
      $wpdb->query($sql);

      $sql = 'DROP TABLE IF EXISTS ' . $this->tables['events_speakers'];
      $wpdb->query($sql);

      $sql = 'DROP TABLE IF EXISTS ' . $this->tables['speakers'];
      $wpdb->query($sql);

      $sql = 'DROP TABLE IF EXISTS ' . $this->tables['venues'];
      $wpdb->query($sql);
  }

  /**
   * insert_example_data()
   *
   * Insert some dummy data
   *
   * @return void
   */
  private function insert_example_data() {
      // Only insert the example data if no data already exists

      $sql = '
          SELECT
              id
          FROM
              '.$this->tables['events'].'
          LIMIT
              1';
      $data_exists = $this->wpdb->get_var($sql);
      if ($data_exists) {
          return false;
      }

      // Insert example data

      $rows = array(
          array(
              'id' => 1,
              'venue_id' => 2,
              'date' => '2011-06-17',
              'time' => '18:00:00',
              'description' => '',
              'is_public' => 1
          ),
          array(
              'id' => 2,
              'venue_id' => 2,
              'date' => '2011-11-10',
              'time' => '15:43:00',
              'description' => '',
              'is_public' => 1
          ),
          array(
              'id' => 3,
              'venue_id' => 1,
              'date' => '2011-08-14',
              'time' => '18:00:00',
              'description' => 'Description about this event...',
              'is_public' => 1
          )
      );
      foreach($rows as $row) {
          $this->wpdb->insert($this->tables['events'], $row);
      }

      $rows = array(
          array(
              'event_id' => 1,
              'speaker_id' => 5
          ),
          array(
              'event_id' => 1,
              'speaker_id' => 4
          ),
          array(
              'event_id' => 2,
              'speaker_id' => 6
          ),
          array(
              'event_id' => 2,
              'speaker_id' => 3
          ),
          array(
              'event_id' => 2,
              'speaker_id' => 2
          ),
          array(
              'event_id' => 3,
              'speaker_id' => 5
          ),
          array(
              'event_id' => 3,
              'speaker_id' => 6
          ),
          array(
              'event_id' => 3,
              'speaker_id' => 3
          )
      );
      foreach($rows as $row) {
          $this->wpdb->insert($this->tables['events_speakers'], $row);
      }

      $rows = array(
          array(
              'id' => 1,
              'first_name' => 'Maurice',
              'last_name' => 'Deebank',
              'url' => 'http://maurice.com',
              'description' => 'Maurice\'s bio...'
          ),
          array(
              'id' => 2,
              'first_name' => 'Gary',
              'last_name' => 'Ainge',
              'url' => 'http://gary.com',
              'description' => 'Gary\'s bio...'
          ),
          array(
              'id' => 3,
              'first_name' => 'Martin',
              'last_name' => 'Duffy',
              'url' => 'http://martin.com',
              'description' => 'Martin\'s bio...'
          ),
          array(
              'id' => 4,
              'first_name' => 'Marco',
              'last_name' => 'Thomas',
              'url' => 'http://marco.com',
              'description' => 'Marco\'s bio...'
          ),
          array(
              'id' => 5,
              'first_name' => 'Nick',
              'last_name' => 'Gilbert',
              'url' => 'http://nick.com',
              'description' => 'Nick\'s bio...'
          ),
          array(
              'id' => 6,
              'first_name' => 'Mick',
              'last_name' => 'Lloyd',
              'url' => 'http://mick.com',
              'description' => 'Mick\'s bio...'
          )
      );
      foreach($rows as $row) {
          $this->wpdb->insert($this->tables['speakers'], $row);
      }

      $rows = array(
          array(
              'id' => 1,
              'name' => 'Cabell Auditorium',
              'sort_name' => 'Cabell Auditorium',
              'url' => 'http://cabellauditorium.com',
              'description' => '',
              'address1' => '10 E 15th St',
              'address2' => '',
              'city' => 'New York',
              'state' => 'NY',
              'zip' => '10003'
          ),
          array(
              'id' => 2,
              'name' => 'Farveson Hall',
              'sort_name' => 'Farveson Hall',
              'url' => 'http://farvesonhall.org',
              'description' => '',
              'address1' => '216 W 21st St',
              'address2' => '',
              'city' => 'New York',
              'state' => 'NY',
              'zip' => '10011'
          )
      );
      foreach($rows as $row) {
          $this->wpdb->insert($this->tables['venues'], $row);
      }
  }
}

?>
