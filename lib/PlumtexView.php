<?php


// Handles working with PHP output templates
class PlumtexView
{
    public $v;

    public function __construct($v)
    {
        $this->v = realpath($v);
    }

    public function set($a)
    {
        foreach ($a as $k => $v) {
            $this->$k = $v;
        }
    }

    public function assign($var, $val)
    {
        $this->$var = $val;
    }

    public function __get_vars()
    {
        ob_start();
        extract((array)$this);

        $file_dir = dirname($this->v) . DIRECTORY_SEPARATOR;

        require $this->v;

        $content = ob_get_clean();
        unset($content);

        $defined_vars = array();
        $var_names = array_keys(get_defined_vars());

        foreach ($var_names as $var_name) {
            if ($var_name != 'defined_vars' and $var_name != 'this') {
                $defined_vars[$var_name] = $$var_name;
            }
        }

        return $defined_vars;
    }

    public function display()
    {
        echo $this->__toString();
    }

    public function __toString()
    {
        extract((array)$this);
        ob_start();
        if (is_array($this->v)) {
            foreach ($this->v as $item) {
                if (is_file($item)) {
                    $res = include $item;
                }
            }
        } elseif (is_string($this->v)) {
            if (is_file($this->v)) {
                $res = include $this->v;
            }
        }

        if (isset($res) and is_object($res)) {
            return $res;
        }
        $content = ob_get_clean();

        return $content;
    }
}
