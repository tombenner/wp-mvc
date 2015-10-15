<?php print("<?php\n\n"); ?>
/**
 * Description of <?php echo $class_name; ?>
 *
 */
class <?php echo $class_name ?> extends WP_Widget {
    public function __construct() {
        parent::__construct('<?php echo $name_underscore; ?>', '<?php echo $title; ?>', $widget_options, $control_options);
    }

    public function form($instance) {
        
        echo '<input type="text" id="'.$this->get_field_id('title').'" name="'.$this->get_field_id('title').'" value="'.$instance['title'].'" />';
        
    }
    
    public function update($new_instance, $old_instance) {
        // Process widget options to be saved
        $instance = array_merge($old_instance, $new_instance);
        return $instance;
    }

    public function widget($args, $instance) {
        // Output the content of the widget
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);

        echo $before_widget;
        if ($title) {
            echo $before_title.$title.$after_title;
        }
            
        echo '
        
            <ul>
                <li>Widget</li>
                <li>view</li>
                <li>to users</li>
                <li>goes here</li>
            </ul>
        
        ';
        
        echo $after_widget;
    }


}
