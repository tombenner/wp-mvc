<?php

class MvcHtmlHelper extends MvcHelper {

    /**
     * Creates an html link
     *
     * @param string $text - title of link
     * @param mixed $url - string path or array of URL parameters i.e. [controller => ..., action => ..., id => ...]
     * @param array $options - array of HTML attributes
     * @param string $confirm_message - javascript confirmation message
     * @return string - string <a /> element
     */
    static function link($text, $url, $options=array(), $confirm_message='') {
        if (is_array($url)) {
            $url = MvcRouter::public_url($url);
        }
        $defaults = array(
            'href' => $url,
            'title' => $text
        );

        $options = array_merge($defaults, $options);

        if (!empty($options['confirm'])) {
            $confirm_message = $options['confirm'];
            unset($options['confirm']);
        }

        if ($confirm_message) {
            $confirm_message = str_replace("'", "\'", $confirm_message);
            $confirm_message = str_replace('"', '\"', $confirm_message);
            $options['onclick'] = "return confirm('{$confirm_message}');";
        }

        $attributes_html = self::attributes_html($options, 'a');
        $html = '<a'.$attributes_html.'>'.$text.'</a>';
        return $html;
    }

    static function object_url($object, $options=array()) {
        $defaults = array(
            'id' => $object->__id,
            'action' => 'show',
            'object' => $object
        );
        $options = array_merge($defaults, $options);
        $url = MvcRouter::public_url($options);
        return $url;
    }

    static function object_link($object, $options=array()) {
        $url = self::object_url($object, $options);
        $text = empty($options['text']) ? $object->__name : $options['text'];
        return self::link($text, $url, $options);
    }

    static function admin_object_url($object, $options=array()) {
        $defaults = array(
            'id' => $object->__id,
            'object' => $object
        );
        $options = array_merge($defaults, $options);
        $url = MvcRouter::admin_url($options);
        return $url;
    }

    static function admin_object_link($object, $options=array()) {
        $url = self::admin_object_url($object, $options);
        $text = empty($options['text']) ? $object->__name : $options['text'];
        return self::link($text, $url);
    }

    public function __call($method, $args) {
        if (property_exists($this, $method)) {
            if (is_callable($this->$method)) {
                return call_user_func_array($this->$method, $args);
            }
        }
    }

}

?>
