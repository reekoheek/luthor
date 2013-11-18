<?php

namespace App\LXC;

class LXC {
    protected static $instance;

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

    public static function getInstance() {
        if (is_null(static::$instance)) {
            $app = \Bono\App::getInstance();
            $config = $app->config('lxc');
            static::$instance = new LXC($config);
        }

        return static::$instance;
    }

    public function exists($name) {
        $indices = $this->getIndices(true);
        return array_key_exists($name, $indices);
    }

    public function getIndices($force = false) {
        if ($force || is_null($this->indices)) {
            if ($handle = opendir($this->config['directory'])) {
                $this->indices = array();
                while (false !== ($entry = readdir($handle))) {
                    if ($entry[0] != '.') {
                        $this->indices[$entry] = $entry;
                    }
                }
                closedir($handle);
            }
        }
        return $this->indices;
    }

    public function getIPAddress($id) {
        $lines = explode("\n", file_get_contents($this->config['directory'].'/'.$id.'/config'));

        foreach ($lines as $line) {
            if (strpos($line, 'lxc.network.ipv4') === 0) {
                $ip = explode('=', $line);
                $ip = trim($ip[1]);
                $ip = explode('/', $ip);
                return $ip[0];
            }
        }

        $result = exec(sprintf('dig @%s %s +short', $this->config['luthor.ip'], $id));
        if ($result) {
            return $result;
        }
    }


    public function findOne($id) {
        if ($this->exists($id)) {

            $info = array();
            $errCode = $this->exec('lxc-info', sprintf('-qn "%s"', $id), $result);

            if (!$errCode && $result) {
                $state = explode(':', $result[0]);
                $pid = explode(':', $result[1]);

                $info['name'] = $id;
                $info['state'] = trim($state[1]);
                $info['pid'] = ($pid[1] == -1) ? NULL : (int) $pid[1];
                $info['state'] = $this->STATES[$info['state']];
            }

            $info['memlimit'] = $this->getMemLimit($id);
            $info['memswlimit'] = $this->getMemSwLimit($id);
            $info['cpus'] = $this->getCPUS($id);
            $info['cpu_shares'] = $this->getCPUShares($id);

            // FIXME load is broken
            return $info; //array_merge($info, $this->load($id));
        }
    }

    public function getMemUsage($id) {
        $result = '';
        $errCode = $this->exec('lxc-cgroup', sprintf('-n %s memory.usage_in_bytes', $id), $result);
        if ($result) {
            return $result[0];
        }
    }

    public function getMemLimit($id) {
        $content = explode("\n", file_get_contents($this->config['directory'].'/'.$id.'/config'));
        foreach ($content as $key => $line) {
            $line = trim($line);
            if (empty($line) || $line[0] == '#') {
                continue;
            }
            if (strpos($line, 'lxc.cgroup.memory.limit_in_bytes') !== false) {
                $line = explode('=',$line);
                return trim($line[1]);
            }
        }
    }

    public function setMemLimit($id, $value) {
        $content = explode("\n", file_get_contents($this->config['directory'].'/'.$id.'/config'));
        $result = array();
        foreach ($content as $key => $line) {
            $line = trim($line);
            if (empty($line) || $line[0] == '#') {
                continue;
            }
            if (strpos($line, 'lxc.cgroup.memory.limit_in_bytes') === false) {
                $result[] = $line;
            }
        }

        if (!empty($value)) {
            $result[] = 'lxc.cgroup.memory.limit_in_bytes = '.$value;
        }


        $tmpFile = tempnam('../tmp', 't');

        file_put_contents($tmpFile, implode("\n", $result));

        $errCode = $this->exec('../bin/luthor-copy', sprintf('"%s" "%s"', $tmpFile, $this->config['directory'].'/'.$id.'/config'), $result);

        unlink($tmpFile);
    }

    public function getMemSwLimit($id) {
        $content = explode("\n", file_get_contents($this->config['directory'].'/'.$id.'/config'));
        foreach ($content as $key => $line) {
            $line = trim($line);
            if (empty($line) || $line[0] == '#') {
                continue;
            }
            if (strpos($line, 'lxc.cgroup.memory.memsw.limit_in_bytes') !== false) {
                $line = explode('=',$line);
                return trim($line[1]);
            }
        }
    }

    public function setMemSwLimit($id, $value) {
        $content = explode("\n", file_get_contents($this->config['directory'].'/'.$id.'/config'));
        $result = array();
        foreach ($content as $key => $line) {
            $line = trim($line);
            if (empty($line) || $line[0] == '#') {
                continue;
            }
            if (strpos($line, 'lxc.cgroup.memory.memsw.limit_in_bytes') === false) {
                $result[] = $line;
            }
        }

        if (!empty($value)) {
            $result[] = 'lxc.cgroup.memory.memsw.limit_in_bytes = '.$value;
        }


        $tmpFile = tempnam('../tmp', 't');

        file_put_contents($tmpFile, implode("\n", $result));

        $errCode = $this->exec('../bin/luthor-copy', sprintf('"%s" "%s"', $tmpFile, $this->config['directory'].'/'.$id.'/config'), $result);

        unlink($tmpFile);
    }

    public function getCPUS($id) {
        $content = explode("\n", file_get_contents($this->config['directory'].'/'.$id.'/config'));
        foreach ($content as $key => $line) {
            $line = trim($line);
            if (empty($line) || $line[0] == '#') {
                continue;
            }
            if (strpos($line, 'lxc.cgroup.cpuset.cpus') !== false) {
                $line = explode('=',$line);
                return trim($line[1]);
            }
        }
    }

    public function setCPUS($id, $value) {
        $content = explode("\n", file_get_contents($this->config['directory'].'/'.$id.'/config'));
        $result = array();
        foreach ($content as $key => $line) {
            $line = trim($line);
            if (empty($line) || $line[0] == '#') {
                continue;
            }
            if (strpos($line, 'lxc.cgroup.cpuset.cpus') === false) {
                $result[] = $line;
            }
        }

        if (!empty($value)) {
            $result[] = 'lxc.cgroup.cpuset.cpus = '.$value;
        }


        $tmpFile = tempnam('../tmp', 't');

        file_put_contents($tmpFile, implode("\n", $result));

        $errCode = $this->exec('../bin/luthor-copy', sprintf('"%s" "%s"', $tmpFile, $this->config['directory'].'/'.$id.'/config'), $result);

        unlink($tmpFile);
    }

    public function getCPUShares($id) {
        $content = explode("\n", file_get_contents($this->config['directory'].'/'.$id.'/config'));
        foreach ($content as $key => $line) {
            $line = trim($line);
            if (empty($line) || $line[0] == '#') {
                continue;
            }
            if (strpos($line, 'lxc.cgroup.cpu.shares') !== false) {
                $line = explode('=',$line);
                return trim($line[1]);
            }
        }
    }

    public function setCPUShares($id, $value) {
        $content = explode("\n", file_get_contents($this->config['directory'].'/'.$id.'/config'));
        $result = array();
        foreach ($content as $key => $line) {
            $line = trim($line);
            if (empty($line) || $line[0] == '#') {
                continue;
            }
            if (strpos($line, 'lxc.cgroup.cpu.shares') === false) {
                $result[] = $line;
            }
        }

        if (!empty($value)) {
            $result[] = 'lxc.cgroup.cpu.shares = '.$value;
        }


        $tmpFile = tempnam('../tmp', 't');

        file_put_contents($tmpFile, implode("\n", $result));

        $errCode = $this->exec('../bin/luthor-copy', sprintf('"%s" "%s"', $tmpFile, $this->config['directory'].'/'.$id.'/config'), $result);

        unlink($tmpFile);
    }

    public function find() {
        $indices = $this->getIndices();
        $containers = array();
        foreach ($indices as $index) {
            $containers[$index] = $this->findOne($index);
        }

        return $containers;
    }

    public function create($options) {
        $name = $options['name'];
        $template = $options['template'];

        if ($this->exists($name)) {
            throw new \Exception(sprintf('Container %s already exists', $name));
        }

        $arg = NULL;
        if (!empty($options['ip_address'])) {
            if (is_null($arg)) $arg = ' --';
            $arg .= ' -i '.$options['ip_address'];
        }

        $errCode = $this->exec('lxc-create', sprintf('-n "%s" -t "%s" %s', $name, $template, $arg), $result);
        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }

        $info = null;
        for($i = 0; $i < 10; $i++) {
            $info = $this->findOne($name);
            if (isset($info)) return $info;
            sleep(1);
        }

        throw new \Exception('Wrong state or something wrong happened in the middle of process');

    }

    public function start($name) {
        $errCode = $this->exec('lxc-start', sprintf('-dn "%s"', $name), $result);
        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }

        $info = null;
        for($i = 0; $i < 5; $i++) {
            $info = $this->findOne($name);
            if ($info['state'] != 0) return $info;
            sleep(1);
        }

        throw new \Exception('Wrong state or something wrong happened in the middle of process');
    }

    public function stop($name) {
        $errCode = $this->exec('lxc-stop', sprintf('-n "%s"', $name), $result);
        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }

        $info = null;
        for($i = 0; $i < 5; $i++) {
            $info = $this->findOne($name);
            if ($info['state'] == 0) return $info;
            sleep(1);
        }

        throw new \Exception('Wrong state or something wrong happened in the middle of process');
    }

    public function destroy($name) {
        $errCode = $this->exec('lxc-destroy', sprintf('-n "%s"', $name), $result);
        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }
    }

    // public function load($id) {
    //     $result = array();
    //     $content = explode("\n", file_get_contents($this->config['directory'].'/'.$id.'/config'));
    //     foreach ($content as $line) {
    //         $line = trim($line);
    //         if (!empty($line) && $line[0] !== '#') {

    //             $l = explode('=', $line, 2);
    //             $result[trim($l[0])] = trim($l[1]);
    //         }
    //     }

    //     return $result;
    // }

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