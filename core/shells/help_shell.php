<?php

/**
 * Help system for the WPMVC Console. 
 * Also provides the capability for shells to be self documenting.
 */
class HelpShell extends MvcShell
{

    protected function init($args)
    {
        parent::init($args);
    }

    protected function get_shell_meta($name)
    {
        $exclude_methods = array(
            "__construct",
            "__call",
            "init",
            "out",
            "nl",
            "hr"
        );

        $shell = new stdClass();
        $shell->name = $name;
        $shell->shell_name = sprintf("%s_shell", $name);
        $shell->class_name = MvcInflector::camelize($shell->shell_name);
        $shell->title = MvcInflector::titleize($shell->name);
        $shell->methods = array();

        $shell_path = sprintf("shells/%s.php", $shell->shell_name);


        $file_path = $this->file_includer->find_first_app_file_or_core_file($shell_path);

        if (!$file_path)
        {
            throw new InvalidArgumentException(sprintf("%s could not be found.\n", $shell->name));
        }

        $result = $this->file_includer->require_first_app_file_or_core_file($shell_path);

        $clazz = new ReflectionClass($shell->class_name);
        $methods = $clazz->getMethods(ReflectionMethod::IS_PUBLIC);

        $shell->doc = $this->parse_doc_block($clazz->getDocComment());

        if (empty($shell->doc))
        {
            $shell->doc = "(No Documentation)";
        }

        foreach ($methods as $method)
        {
            $method_name = $method->getName();
            $method_doc_block = $this->parse_doc_block($method->getDocComment());

            if (!in_array($method_name, $exclude_methods))
            {
                if (empty($method_doc_block))
                {
                    $method_doc_block = "(No Documentation)";
                }

                if ($method_name == "main")
                {
                    $method_name = "(default)";  // hack to make default first
                }

                $shell->methods[$method_name] = $method_doc_block;
            }
        }


        ksort($shell->methods);
        
        
        return $shell;
    }

    protected function get_available_shells()
    {
        $exclude = array(
            'mvc_shell.php',
            'mvc_shell_dispatcher.php'
        );

        $pluginAppPaths = MvcConfiguration::get('PluginAppPaths');
        $pluginAppPaths["core"] = MVC_CORE_PATH;

        $shells = array();

        foreach ($pluginAppPaths as $plugin => $path)
        {
            $path = sprintf("%s/shells", $path);

            $files = $this->file_includer->get_php_files_in_directory($path);

            $key = MvcInflector::camelize($plugin);

            $shells[$plugin] = array();

            foreach ($files as $file)
            {
                if (!in_array($file, $exclude))
                {
                    $name = str_replace("_shell.php", "", $file);
                    $shells[$plugin][] = $name;
                }
            }
        }

        return $shells;
    }

    /**
     * Get a list of the available shells.
     * Also is executed if the wpmvc console is run with no arguments.
     * 
     * @param mixed $args 
     */
    public function main($args)
    {
        $shells = $this->get_available_shells();
        $this->out("Available Shells:");


        $table = new Console_Table(
                        CONSOLE_TABLE_ALIGN_LEFT,
                        " ",
                        1,
                        null,
                        true /* this is important when using Console_Color */
        );

        foreach ($shells as $plugin => $shells)
        {

            $pluginLabel = Console_Color::convert("%W" . MvcInflector::camelize($plugin) . "%n");

            for ($i = 0; $i < count($shells); $i++)
            {
                if ($i > 0)
                {
                    $pluginLabel = " ";
                }

                $table->addRow(array(
                    $pluginLabel,
                    MvcInflector::camelize($shells[$i])
                ));
            }

            $table->addSeparator();
        }

        $this->out($table->getTable());

        $this->out("To get information about a shell try:");
        $this->out("\n\twpmvc help shell <name_of_shell>");
    }

    protected function parse_doc_block($text)
    {
        $doc = preg_replace("/^\\s*(\\/|\\*+)[\\*]*([\\/]|[\\s{1}]\\@.*|[\\ ]*|)/um", "", $text);

        return trim($doc);
    }

    /**
     * Show documentation for a shell.
     * Usage:
     * wpmvc Help Shell <shell_name> [command_name]
     * wpmvc Help Shell Generate
     * wpmvc Help Shell Generate Scaffold
     * @param mixed $args
     * @return null 
     */
    public function shell($args)
    {
        list($name, $method) = $args;

        if (empty($name))
        {
            $this->out("No shell given");
            return;
        }

        try
        {
            $shell = $this->get_shell_meta($name);
        }
        catch (Exception $ex)
        {
            $this->out("An Error Occurred");
            $this->out($ex->getMessage());
            return;
        }



        $this->nl();
        $this->out(Console_Color::convert("%UShells > %n%U%9" . $shell->title . "%n"));
        $this->nl();
        $this->out($shell->doc);
        $this->nl(2);
        $this->out("Commands:");

        $table = new Console_Table(
                        CONSOLE_TABLE_ALIGN_LEFT,
                        " ",
                        1,
                        null,
                        true /* this is important when using Console_Color */
        );

        if($method == "default") {
            $method = "(default)"; // hack to choose default
        }
        
        if (!empty($method) && !empty($shell->methods[$method]))
        {
            $table->addRow(array(Console_Color::convert("%9" . $method . "%n"), $shell->methods[$method]));
        }
        else
        {

            foreach ($shell->methods as $method => $doc)
            {

                $table->addRow(array(Console_Color::convert("%9" . $method . "%n"), $doc));
                $table->addSeparator();
            }
        }

        $this->out($table->getTable());
    }

}

?>
