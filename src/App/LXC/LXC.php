<?php

namespace App\LXC;

class LXC {
    protected $STATES = array(
        'STOPPED' => 0,
        'RUNNING' => 1,
        'FROZEN' => 2,
    );

    protected $indices;

    protected $templates;

    public function __construct($config) {
        $this->config = $config;
    }

    public function exists($name) {
        $indices = $this->getIndices();
        return array_key_exists($name, $indices);
    }

    public function getIndices() {
        if (is_null($this->indices) && $handle = opendir($this->config['directory'])) {
            $this->indices = array();
            while (false !== ($entry = readdir($handle))) {
                if ($entry[0] != '.') {
                    $this->indices[$entry] = $entry;
                }
            }
            closedir($handle);
        }
        return $this->indices;
    }


    public function getInfo($id) {
        if ($this->exists($id)) {

            $info = array();
            $errCode = $this->exec('lxc-info', sprintf('-qn "%s"', $id), $result);

            if (!$errCode && $result) {
                $state = explode(':', $result[0]);
                $pid = explode(':', $result[1]);
                $info['$id'] = $id;
                $info['name'] = $id;
                $info['state'] = trim($state[1]);
                $info['pid'] = (int) $pid[1];


                $info['state'] = $this->STATES[$info['state']];

                if ($info['state'] === 1) {
                    $result = exec(sprintf('dig @%s %s +short', $this->config['luthor.ip'], $id));
                    if ($result) {
                        $info['ip_address'] = $result;
                    }

                    $errCode = $this->exec('lxc-cgroup', sprintf('-n %s memory.usage_in_bytes', $id), $result);
                    if ($result) {
                        $info['mem_usage'] = $result[0];
                    }
                }

            }

            return array_merge($info, $this->load($id));
        }
    }

    public function find() {
        $indices = $this->getIndices();
        $containers = array();
        foreach ($indices as $index) {
            $containers[$index] = $this->getInfo($index);
        }

        return $containers;
    }

    public function create($name, $template) {
        if ($this->exists($name)) {
            throw new \Exception(sprintf('Container %s already exists', $name));
        }

        $errCode = $this->exec('lxc-create', sprintf('-n "%s" -t "%s"', $name, $template), $result);
        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }
    }

    public function start($name) {
        $errCode = $this->exec('lxc-start', sprintf('-dn "%s"', $name), $result);
        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }
    }

    public function stop($name) {
        $errCode = $this->exec('lxc-stop', sprintf('-n "%s"', $name), $result);
        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }
    }

    public function destroy($name) {
        $errCode = $this->exec('lxc-destroy', sprintf('-n "%s"', $name), $result);
        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }
    }

    public function getTemplates() {
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

    public function findTemplates() {
        $entries = array();
        $templates = $this->getTemplates();
        foreach ($templates as $template) {
            $entries[] = array(
                '$id' => $template,
                'name' => $template,
                'filename' => $this->config['templatesDirectory'].'/'.$template,
            );
        }
        return $entries;
    }

    public function getTemplate($id) {
        $file = $this->config['templatesDirectory'].'/lxc-'.$id;

        if (is_readable($file)) {
            $result = array(
                '$id' => $id,
                'name' => $id,
                'filename' => $file,
                'content' => file_get_contents($file),
            );
            return $result;
        }

    }

    public function saveTemplate($id, $template) {
        if (is_null($id)) {
            $id = $template['name'];
        }

        $file = $this->config['templatesDirectory'].'/lxc-'.$id;

        $tmpFile = tempnam('../tmp', 't');

        $content = str_replace("\r", "", $template['content']);

        file_put_contents($tmpFile, $content);

        $errCode = $this->exec('../bin/luthor-copy', sprintf('"%s" "%s" 0755', $tmpFile, $file), $result);

        unlink($tmpFile);

        return ($errCode) ? false : true;

    }

    public function deleteTemplate($id) {
        $file = $this->config['templatesDirectory'].'/lxc-'.$id;

        $a = $this->exec('../bin/luthor-delete', $file, $result);

    }

    public function load($id) {
        $result = array();
        $content = explode("\n", file_get_contents($this->config['directory'].'/'.$id.'/config'));
        foreach ($content as $line) {
            $line = trim($line);
            if (!empty($line) && $line[0] !== '#') {

                $l = explode('=', $line, 2);
                $result[trim($l[0])] = trim($l[1]);
            }
        }

        return $result;
    }

    public function save($id, $info) {
        $toSave = array();
        foreach ($info as $key => $value) {
            if (strpos($key, '.') !== false) {
                $toSave[$key] = $value;
            }
        }

        $tmpFile = tempnam('../tmp', 't');

        $f = fopen($tmpFile, 'w');
        foreach ($toSave as $key => $value) {
            fputs($f, $key.' = '.$value."\n");
        }
        fclose($f);

        $errCode = $this->exec('../bin/luthor-copy', sprintf('"%s" "%s"', $tmpFile, $this->config['directory'].'/'.$id.'/config'), $result);

        unlink($tmpFile);

        return 1;
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