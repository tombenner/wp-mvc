<?php print("<?php\n\n"); ?>
/**
 * Description of <?php echo $class_name; ?>
 *
 */
class <?php echo $class_name ?> extends WP_Widget
{
    public function __construct() 
    {
        parent::__construct("<?php echo $name_underscore; ?>", "<?php echo $title; ?>", $widget_options, $control_options);
    }

    public function form($instance)
    {
        <?php echo "?>"; ?>
        
        <input type="text" 
               id="<?php echo("<?php echo \$this->get_field_id('title'); ?>"); ?>"
               name="<?php echo("<?php echo \$this->get_field_name('title'); ?>"); ?>"
               value="<?php echo("<?php echo \$instance['title']; ?>"); ?>"
               />
        
        <?php echo "<?php"; ?>
        
    }
    

    public function update($new_instance, $old_instance)
    {
		// processes widget options to be saved
		$instance = array_merge($old_instance, $new_instance);
		return $instance;
    }

    public function widget($args, $instance)
    {
        // outputs the content of the widget
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
            
		<?php echo "?>"; ?>
		
            <ul>
                <li>Widget</li>
                <li>view</li>
                <li>to users</li>
                <li>goes here</li>
            </ul>
		
		<?php echo "<?php"; ?>
        
        echo $after_widget;
    }


}
