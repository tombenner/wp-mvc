<?php

class EventsCalendarExampleLoader extends MvcPluginLoader {

    var $db_version = '1.0';
    
    function init() {
    
        // Include any code here that needs to be called when this class is instantiated
    
        global $wpdb;
    
        $this->tables = array(
            'events' => $wpdb->prefix.'events',
            'events_speakers' => $wpdb->prefix.'events_speakers',
            'speakers' => $wpdb->prefix.'speakers',
            'venues' => $wpdb->prefix.'venues'
        );
    
    }

    function activate() {
    
        // This call needs to be made to activate this app within WP MVC
        
        $this->activate_app(__FILE__);
        
        // Perform any databases modifications related to plugin activation here, if necessary

        require_once ABSPATH.'wp-admin/includes/upgrade.php';
    
        add_option('events_calendar_example_db_version', $this->db_version);
    
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

    function deactivate() {
    
        // This call needs to be made to deactivate this app within WP MVC
        
        $this->deactivate_app(__FILE__);
        
        // Perform any databases modifications related to plugin deactivation here, if necessary
    
    }
    
    function insert_example_data() {
    
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
