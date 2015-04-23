<?php echo "<?php\n"; ?>

class <?php echo $name_camelized; ?>Loader extends MvcPluginLoader {

    var $db_version = '1.0';
    var $tables = array();

    function activate() {
    
        // This call needs to be made to activate this app within WP MVC
        
        $this->activate_app(__FILE__);
        
        // Perform any databases modifications related to plugin activation here, if necessary

        require_once ABSPATH.'wp-admin/includes/upgrade.php';
    
        add_option('<?php echo $name_underscored; ?>_db_version', $this->db_version);
        
        // Use dbDelta() to create the tables for the app here
        // $sql = '';
        // dbDelta($sql);
        
    }

    function deactivate() {
    
        // This call needs to be made to deactivate this app within WP MVC
        
        $this->deactivate_app(__FILE__);
        
        // Perform any databases modifications related to plugin deactivation here, if necessary
    
    }

}

<?php echo '?>'; ?>