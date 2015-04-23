<?php

class VenuesShell extends MvcShell {

    public function init() {
        $this->load_model('Venue');
    }
    
    // This updates the sort_name values of all venues; it can be run using "wpmvc venues update_all_sort_names"
    public function update_all_sort_names($args) {
        $venues = $this->Venue->find();
        foreach($venues as $venue) {
            $this->Venue->update_sort_name($venue);
        }
        $this->out('Successfully updated the sort_name values for all venues.');
    }

}

?>