<?php

class MvcError {

    public static function fatal($message) {
        self::write('fatal', $message);
        die();
    }

    public static function warning($message) {
        self::write('warning', $message);
    }

    public static function notice($message) {
        self::write('notice', $message);
    }
    
    private static function write($type_key, $message) {
    
        $type_name = self::get_type($type_key);
        
        $context = self::get_context();
        $line = $context['line'];
        $file = $context['file'];
        
        $execution_context = MvcConfiguration::get('ExecutionContext');
        
        if ($execution_context == 'shell') {
        
            echo '-- '.$type_name.': '.$message."\n".'   (Thrown on line '.$line.' of '.$file.")\n";
        
        } else {
        
            echo '
                <br />
                <strong>[MVC] '.$type_name.'</strong>: '.$message.'
                <br />
                Thrown on line '.$line.' of '.$file.' <br />';
                
        }
    
    }
    
    private static function get_type($type_key) {
    
        $types = array(
            'fatal' => 'Fatal Error',
            'warning' => 'Warning',
            'notice' => 'Notice'
        );
        
        return $types[$type_key];
    
    }
    
    private static function get_context() {
    
        $backtrace = debug_backtrace();
        
        $context = empty($backtrace[3]['line']) ? $backtrace[2] : $backtrace[3];
        
        return $context;
    
    }

}

?>
