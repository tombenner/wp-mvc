<?php

class MvcDatabase {

    private $wpdb;
    private $debug = true;

    function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->debug = MvcConfiguration::get('Debug');
    }
    
    public function get_results($string, $output_type=OBJECT) {
        $this->add_to_log($string);
        return $this->wpdb->get_results($string, $output_type);
    }
    
    public function get_var($string, $column_offset=0, $row_offset=0) {
        $this->add_to_log($string);
        return $this->wpdb->get_var($string, $column_offset, $row_offset);
    }
    
    public function query($string) {
        $this->add_to_log($string);
        return $this->wpdb->query($string);
    }
    
    public function insert_id() {
        return $this->wpdb->insert_id;
    }
    
    public function escape($string) {
        return esc_sql($string);
    }
    
    public function escape_array($array) {
        foreach ($array as $key => $value) {
            $array[$key] = $this->escape($value);
        }
        return $array;
    }
    
    private function add_to_log($string) {
        if ($this->debug) {
            echo '<pre>'.$string.'</pre>';
        }
    }

}

?>
