<?php

namespace App\LXC;

class TemplateList
{

    /**
     * Singleton var
     * @var App\LXC\TemplateList
     */
    protected static $instance;

    protected $config;
    protected $entries;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Singleton getter
     * @return App\LXC\ContainerList The singleton
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            $app = \Bono\App::getInstance();
            $config = $app->config('lxc');
            static::$instance = new static($config);
        }

        return static::$instance;
    }

    public function newInstance()
    {
        return new Template(null, $this->config);
    }

    public function find()
    {

        if (empty($this->entries) && $handle = opendir($this->config['templatesDirectory'])) {
            $templates = array();
            while (false !== ($entry = readdir($handle))) {
                if (is_file($this->config['templatesDirectory'].'/'.$entry) && strpos($entry, 'lxc-')  === 0) {
                    $entry = substr($entry, 4);
                    $templates[$entry] = $entry;
                }
            }
            closedir($handle);

            $entries = array();
            foreach ($templates as $template) {
                $entries[$template] = $this->findOne($template);
            }

            $this->entries = $entries;

        }

        return $this->entries;

    }

    public function findOne($name)
    {
        $filename = $this->config['templatesDirectory'].'/lxc-'.$name;

        if (is_readable($filename)) {
            $result = new Template(null, $this->config);
            $result['name'] = $name;
            return $result;
        }
    }
}
