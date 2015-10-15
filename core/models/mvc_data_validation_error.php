<?php

class MvcDataValidationError {

    private $errors = array();
    
    function __construct($field=null, $message=null) {
        if ($field && $message) {
            $this->add_error($field, $message);
        }
    }
    
    public function add_error($field, $message) {
        $this->errors[] = array(
            'field' => $field,
            'message' => $message
        );
    }
    
    public function get_errors() {
        return $this->errors;
    }
    
    public function get_html() {
        if (empty($this->errors)) {
            return false;
        }
        $html = '';
        foreach ($this->errors as $error) {
            $html .= '
                <div class="mvc-validation-error">
                    '.$error['message'].'
                </div>';
        }
        return $html;
    }

}

?>