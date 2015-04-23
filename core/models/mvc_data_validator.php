<?php

class MvcDataValidator {

    private $common_patterns = array(
        'hostname' => '(?:[a-z0-9][-a-z0-9]*\.)*(?:[a-z0-9][-a-z0-9]{0,62})\.(?:(?:[a-z]{2}\.)?[a-z]{2,4}|museum|travel)'
    );
    
    public function validate($field, $value, $rule) {
        if (is_string($rule)) {
            if (method_exists($this, $rule)) {
                $result = $this->{$rule}($value);
                if ($result === true) {
                    return true;
                }
                $message = $result;
                $message = $this->process_message($message, $field);
                $error = new MvcDataValidationError($field, $message);
                return $error;
            } else {
                MvcError::fatal('The validation rule "'.$rule.'" wasn\'t found.');
            }
        }
        if (is_array($rule)) {
            if(!empty($rule['pattern']) || !empty($rule['rule'])) {
                return $this->validate_using_array_rule($field, $value, $rule);
            }
        }
        MvcError::fatal('The validation rule "'.print_r($rule, true).'" wasn\'t defined correctly.');
    }
    
    private function validate_using_array_rule($field, $value, $rule) {
        $message = '';
        if (isset($rule['required']) && !$rule['required']) {
            if (empty($value)) {
                return true;
            }
        }
        if (isset($rule['pattern'])) {
            $valid = preg_match($rule['pattern'], $value);
        } else if (isset($rule['rule'])) {
            if (method_exists($this, $rule['rule'])) {
                $result = $this->{$rule['rule']}($value);
                $valid = $result === true ? true : false;
                $message = $result;
            }
        }
        if ($valid) {
            return true;
        }
        if (!empty($rule['message'])) {
            $message = $rule['message'];
        }
        $message = $this->process_message($message, $field);
        $error = new MvcDataValidationError($field, $message);
        return $error;
    }
    
    private function matches_pattern($value, $pattern) {
        return preg_match($pattern, $value);
    }
    
    private function process_message($message, $field) {
        $titleized_field = MvcInflector::titleize($field);
        $message = str_replace('{field}', $titleized_field, $message);
        return $message;
    }
    
    private function alphanumeric($value) {
        $pattern = '/[\w]+/';
        if ($this->matches_pattern($value, $pattern)) {
            return true;
        } else {
            return '{field} must only contain letters and numbers.';
        }
    }
    
    private function numeric($value) {
        if (is_numeric($value)) {
            return true;
        } else {
            return '{field} must be a number.';
        }
    }
    
    private function email($value) {
        $pattern = '/^[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@'.$this->common_patterns['hostname'].'$/i';
        if ($this->matches_pattern($value, $pattern)) {
            return true;
        } else {
            return '{field} must be a valid email address.';
        }
    }
    
    private function not_empty($value) {
        $pattern = '/[^\s]+/';
        if ($this->matches_pattern($value, $pattern)) {
            return true;
        } else {
            return '{field} can\'t be empty.';
        }
    }
    
    private function url($value) {
        $this->populate_ip_patterns();
        $valid_chars = '(['.preg_quote('!"$&\'()*+,-.@_:;=~').'\/0-9a-z]|(%[0-9a-f]{2}))';
        $pattern = '/^(?:(?:https?|ftps?|file|news|gopher):\/\/)?'.
            '(?:'.$this->common_patterns['ipv4'].'|\['.$this->common_patterns['ipv6'].'\]|'.$this->common_patterns['hostname'].')'.
            '(?::[1-9][0-9]{0,4})?'.
            '(?:\/?|\/'.$valid_chars.'*)?'.
            '(?:\?'.$valid_chars.'*)?'.
            '(?:#'.$valid_chars.'*)?$/i';
        if ($this->matches_pattern($value, $pattern)) {
            return true;
        } else {
            return '{field} must be a valid URL.';
        }
    }
    
    private function populate_ip_patterns() {
        if (!isset($this->common_patterns['ipv6'])) {
            $pattern = '((([0-9A-Fa-f]{1,4}:){7}(([0-9A-Fa-f]{1,4})|:))|(([0-9A-Fa-f]{1,4}:){6}'.
            '(:|((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})'.
            '|(:[0-9A-Fa-f]{1,4})))|(([0-9A-Fa-f]{1,4}:){5}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})'.
            '(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)'.
            '{4}(:[0-9A-Fa-f]{1,4}){0,1}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2}))'.
            '{3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){3}(:[0-9A-Fa-f]{1,4}){0,2}'.
            '((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|'.
            '((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:){2}(:[0-9A-Fa-f]{1,4}){0,3}'.
            '((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2}))'.
            '{3})?)|((:[0-9A-Fa-f]{1,4}){1,2})))|(([0-9A-Fa-f]{1,4}:)(:[0-9A-Fa-f]{1,4})'.
            '{0,4}((:((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)'.
            '|((:[0-9A-Fa-f]{1,4}){1,2})))|(:(:[0-9A-Fa-f]{1,4}){0,5}((:((25[0-5]|2[0-4]'.
            '\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})?)|((:[0-9A-Fa-f]{1,4})'.
            '{1,2})))|(((25[0-5]|2[0-4]\d|[01]?\d{1,2})(\.(25[0-5]|2[0-4]\d|[01]?\d{1,2})){3})))(%.+)?';
            $this->common_patterns['ipv6'] = $pattern;
        }
        if (!isset($this->common_patterns['ipv4'])) {
            $pattern = '(?:(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])\.){3}(?:25[0-5]|2[0-4][0-9]|(?:(?:1[0-9])?|[1-9]?)[0-9])';
            $this->common_patterns['ipv4'] = $pattern;
        }
    }

}

?>