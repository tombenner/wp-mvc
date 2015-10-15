<?php

class MvcInflector {

    // Plural inflector rules
    protected static $_plural = array(
        'rules' => array(
            '/status$/i' => 'statuses',
            '/quiz$/i' => 'quizzes',
            '/^ox$/i' => '\oxen',
            '/([m|l])ouse$/i' => '\1ice',
            '/(matr|vert|ind)(ix|ex)$/i' => '\1ices',
            '/(x|ch|ss|sh)$/i' => '\1es',
            '/([^aeiouy]|qu)y$/i' => '\1ies',
            '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
            '/sis$/i' => 'ses',
            '/([ti])um$/i' => '\1a',
            '/person$/i' => 'people',
            '/man$/i' => 'men',
            '/child$/i' => 'children',
            '/(buffal|tomat)o$/i' => '\1\2oes',
            '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
            '/us$/i' => 'uses',
            '/alias$/i' => 'aliases',
            '/(ax|cris|test)is$/i' => '\1es',
            '/s$/' => 's',
            '/^$/' => '',
            '/$/' => 's',
        ),
        'uninflected' => array(
            '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', 'people'
        ),
        'irregular' => array(
            'atlas' => 'atlases',
            'beef' => 'beefs',
            'brother' => 'brothers',
            'cafe' => 'cafes',
            'child' => 'children',
            'corpus' => 'corpuses',
            'cow' => 'cows',
            'ganglion' => 'ganglions',
            'genie' => 'genies',
            'genus' => 'genera',
            'graffito' => 'graffiti',
            'hoof' => 'hoofs',
            'loaf' => 'loaves',
            'man' => 'men',
            'money' => 'monies',
            'mongoose' => 'mongooses',
            'move' => 'moves',
            'mythos' => 'mythoi',
            'niche' => 'niches',
            'numen' => 'numina',
            'occiput' => 'occiputs',
            'octopus' => 'octopuses',
            'opus' => 'opuses',
            'ox' => 'oxen',
            'person' => 'people',
            'sex' => 'sexes',
            'soliloquy' => 'soliloquies',
            'trilby' => 'trilbys',
            'turf' => 'turfs'
        )
    );

    // Singular inflector rules
    protected static $_singular = array(
        'rules' => array(
            '/statuses$/i' => 'status',
            '/quizzes$/i' => 'quiz',
            '/matrices$/i' => 'matrix',
            '/(vert|ind)ices$/i' => '\1ex',
            '/^oxen/i' => 'ox',
            '/aliases$/i' => 'alias',
            '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
            '/([ftw]ax)es/i' => '\1',
            '/(cris|ax|test)es$/i' => '\1is',
            '/(shoe|slave)s$/i' => '\1',
            '/oes$/i' => 'o',
            '/ouses$/' => 'ouse',
            '/([^a])uses$/' => '\1us',
            '/([m|l])ice$/i' => '\1ouse',
            '/(x|ch|ss|sh)es$/i' => '\1',
            '/([^aeiouy]|qu)ies$/i' => '\1y',
            '/([lr])ves$/i' => '\1f',
            '/([^fo])ves$/i' => '\1fe',
            '/^analyses$/i' => 'analysis',
            '/(analy|ba|diagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
            '/([ti])a$/i' => '\1um',
            '/people$/i' => 'person',
            '/men$/i' => 'man',
            '/children$/i' => 'child',
            '/eaus$/' => 'eau',
            '/^(.*us)$/' => '\\1',
            '/s$/i' => ''
        ),
        'uninflected' => array(
            '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', '.*ss'
        ),
        'irregular' => array(
            'waves' => 'wave',
            'curves' => 'curve'
        )
    );

    // Words that should not be inflected
    protected static $_uninflected = array(
        'Amoyese', 'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus',
        'carp', 'chassis', 'clippers', 'cod', 'coitus', 'Congoese', 'contretemps', 'corps',
        'debris', 'diabetes', 'djinn', 'eland', 'elk', 'equipment', 'Faroese', 'flounder',
        'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
        'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings',
        'jackanapes', 'Kiplingese', 'Kongoese', 'Lucchese', 'mackerel', 'Maltese', 'media',
        'mews', 'moose', 'mumps', 'Nankingese', 'news', 'nexus', 'Niasese',
        'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese',
        'proceedings', 'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors',
        'sea[- ]bass', 'series', 'Shavese', 'shears', 'siemens', 'species', 'swine', 'testes',
        'trousers', 'trout','tuna', 'Vermontese', 'Wenchowese', 'whiting', 'wildebeest',
        'Yengeese'
    );
    
    protected static $_cache = array();
    
    protected static function set_cached_patterns() {
    
        self::$_singular['merged'] = array();
        self::$_singular['merged']['irregular'] = array_merge(self::$_singular['irregular'], array_flip(self::$_plural['irregular']));
        self::$_singular['merged']['uninflected'] = array_merge(self::$_singular['uninflected'], self::$_uninflected);
        
        self::$_singular['cached'] = array();
        self::$_singular['cached']['irregular'] = '(?:'.join('|', array_keys(self::$_singular['merged']['irregular'])).')';
        self::$_singular['cached']['uninflected'] = '(?:'.join('|', self::$_singular['merged']['uninflected']).')';
        
        self::$_plural['merged'] = array();
        self::$_plural['merged']['irregular'] = array_merge(self::$_plural['irregular'], array_flip(self::$_singular['irregular']));
        self::$_plural['merged']['uninflected'] = array_merge(self::$_plural['uninflected'], self::$_uninflected);
        
        self::$_plural['cached'] = array();
        self::$_plural['cached']['irregular'] = '(?:'.join('|', array_keys(self::$_plural['merged']['irregular'])).')';
        self::$_plural['cached']['uninflected'] = '(?:'.join('|', self::$_plural['merged']['uninflected']).')';
        
    }
    
    public static function class_name_from_filename($filename) {
        return MvcInflector::camelize(str_replace('.php', '', $filename));
    }
    
    public static function camelize($string) {
        $string = str_replace('_', ' ', $string);
        $string = str_replace('-', ' ', $string);
        $string = ucwords($string);
        $string = str_replace(' ', '', $string);
        return $string;
    }
    
    public static function tableize($string) {
        $string = MvcInflector::underscore($string);
        $string = MvcInflector::pluralize($string);
        return $string;
    }
    
    public static function underscore($string) {
        $string = preg_replace('/[A-Z]/', ' $0', $string);
        $string = trim(strtolower($string));
        $string = str_replace(' ', '_', $string);
        return $string;
    }
    
    public static function pluralize($string) {
    
        if (isset(self::$_cache['pluralize'][$string])) {
            return self::$_cache['pluralize'][$string];
        }
        
        if (!isset($_plural['cached'])) {
            self::set_cached_patterns();
        }
    
        if (preg_match('/(.*)\\b('.self::$_plural['cached']['irregular'].')$/i', $string, $regs)) {
            self::$_cache['pluralize'][$string] = $regs[1].substr($string, 0, 1).substr(self::$_plural['merged']['irregular'][strtolower($regs[2])], 1);
            return self::$_cache['pluralize'][$string];
        }

        if (preg_match('/^('.self::$_plural['cached']['uninflected'].')$/i', $string, $regs)) {
            self::$_cache['pluralize'][$string] = $string;
            return $string;
        }

        foreach (self::$_plural['rules'] as $rule => $replacement) {
            if (preg_match($rule, $string)) {
                self::$_cache['pluralize'][$string] = preg_replace($rule, $replacement, $string);
                return self::$_cache['pluralize'][$string];
            }
        }
        
        self::$_cache['pluralize'][$string] = $string;
        
        return $string;
        
    }
    
    public static function singularize($string) {

        if (isset(self::$_cache['singularize'][$string])) {
            return self::$_cache['singularize'][$string];
        }
        
        if (!isset($_singular['cached'])) {
            self::set_cached_patterns();
        }
        
        if (preg_match('/(.*)\\b('.self::$_singular['cached']['irregular'].')$/i', $string, $regs)) {
            self::$_cache['singularize'][$string] = $regs[1].substr($string, 0, 1).substr(self::$_singular['merged']['irregular'][strtolower($regs[2])], 1);
            return self::$_cache['singularize'][$string];
        }

        if (preg_match('/^('.self::$_singular['cached']['uninflected'].')$/i', $string, $regs)) {
            self::$_cache['singularize'][$string] = $string;
            return $string;
        }

        foreach (self::$_singular['rules'] as $rule => $replacement) {
            if (preg_match($rule, $string)) {
                self::$_cache['singularize'][$string] = preg_replace($rule, $replacement, $string);
                return self::$_cache['singularize'][$string];
            }
        }
        
        self::$_cache['singularize'][$string] = $string;
        
        return $string;
        
    }
    
    public static function titleize($string) {
        $string = preg_replace('/[A-Z]/', ' $0', $string);
        $string = trim(str_replace('_', ' ', $string));
        $string = ucwords($string);
        return $string;
    }
    
    public static function pluralize_titleize($string) {
        $string = MvcInflector::pluralize(MvcInflector::titleize($string));
        return $string;
    }

    public static function rules($type, $rules, $reset = false) {

        $variable_name = '_'.$type;
        
        foreach ($rules as $rule => $pattern) {
            if (is_array($pattern)) {
                if ($reset) {
                    self::${$var}[$rule] = $pattern;
                } else {
                    if ($rule === 'uninflected') {
                        self::${$variable_name}[$rule] = array_merge($pattern, self::${$variable_name}[$rule]);
                    } else {
                        self::${$variable_name}[$rule] = $pattern + self::${$variable_name}[$rule];
                    }
                }
                unset($rules[$rule], self::${$variable_name}['cached'][$rule]);
                if (isset(self::${$variable_name}['merged'][$rule])) {
                    unset(self::${$variable_name}['merged'][$rule]);
                }
                if ($type === 'plural') {
                    self::$_cache['pluralize'] = self::$_cache['tableize'] = array();
                } elseif ($type === 'singular') {
                    self::$_cache['singularize'] = array();
                }
            }
        }
        
        self::${$variable_name}['rules'] = $rules + self::${$variable_name}['rules'];
        
    }

}

?>