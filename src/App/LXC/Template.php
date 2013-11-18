<?php

namespace App\LXC;

class Template {

    protected $config;
    protected $templates;

    public function __construct($config) {
        $this->config = $config;
    }

    public function find() {
        $entries = array();
        $templates = $this->ls();

        foreach ($templates as $template) {
            $entries[$template] = $this->findOne($template);
        }
        return $entries;
    }

    public function findOne($id) {
        $file = $this->config['templatesDirectory'].'/lxc-'.$id;

        if (is_readable($file)) {
            $result = array(
                'name' => $id,
                'filename' => $file,
                'content' => file_get_contents($file),
            );

            if (strpos($result['content'], '# !luthor') !== FALSE) {
                preg_match('/\#\s+\!luthor\s+(.*)/', $result['content'], $matches);
                $result['luthor_version'] = $matches[1];
            }

            return $result;
        }

    }

    public function ls() {
        if (is_null($this->templates) && $handle = opendir($this->config['templatesDirectory'])) {
            $this->templates = array();
            while (false !== ($entry = readdir($handle))) {
                if (is_file($this->config['templatesDirectory'].'/'.$entry) && strpos($entry, 'lxc-')  === 0) {
                    $entry = substr($entry, 4);
                    $this->templates[$entry] = $entry;
                }
            }
            closedir($handle);
        }
        return $this->templates;
    }

    public function save($id, $template) {
        if (is_null($id)) {
            $id = $template['name'];
        }

        $file = $this->config['templatesDirectory'].'/lxc-'.$id;

        $tmpFile = tempnam('../tmp', 't');

        $content = str_replace("\r", "", $template['content']);

        file_put_contents($tmpFile, $content);

        $errCode = $this->exec('../bin/luthor-copy', sprintf('"%s" "%s" 0755', $tmpFile, $file), $result);

        unlink($tmpFile);

        return $this->findOne($id);

        // return ($errCode) ? false : true;

    }

    public function delete($id) {
        $file = $this->config['templatesDirectory'].'/lxc-'.$id;

        $a = $this->exec('../bin/luthor-delete', $file, $result);

    }

    protected function exec($exe, $argString, &$result) {
        if (isset($this->config['executables'][$exe])) {
            $exe = $this->config['executables'][$exe];
        }
        $cmd = sprintf('sudo %s %s', $exe, $argString);

        // var_dump($cmd);
        exec($cmd, $result, $errCode);

        return $errCode;
    }

}