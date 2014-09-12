<?php

/**
 * Luthor - LXC Administration
 *
 * MIT LICENSE
 *
 * Copyright (c) 2013 PT Sagara Xinix Solusitama
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author      Ganesha <reekoheek@gmail.com>
 * @copyright   2014 PT Sagara Xinix Solusitama
 * @link        http://xinix.co.id/products/luthor
 * @license     http://xinix.co.id/products/luthor/LICENSE
 * @package     Luthor
 *
 */
namespace App\LXC;

/**
 * Simple LXC API
 */
class LXC {

    /**
     * Available states
     * @var array
     */
    public static $STATES = array(
        'STOPPED'   => 0,
        'RUNNING'   => 1,
        'FROZEN'    => 2,
    );

    public static $KEY = array(
        'ID'            => 'luthor.id',
        'IP_ADDRESS'    => 'luthor.network.ipv4',
        'MEM_LIMIT'     => 'lxc.cgroup.memory.limit_in_bytes',
        'MEMSW_LIMIT'   => 'lxc.cgroup.memory.memsw.limit_in_bytes',
        'CPUS'          => 'lxc.cgroup.cpuset.cpus',
        'CPU_SHARES'    => 'lxc.cgroup.cpu.shares',
        'NETWORK_IPV4'  => 'lxc.network.ipv4',
        'NETWORK_LINK'  => 'lxc.network.link',
        'NETWORK_REPORT'=> 'luthor.network.report',
        'NETWORK_NETMASK'=> 'luthor.network.netmask',
        'NETWORK_GATEWAY'=> 'luthor.network.gateway',
    );

    /**
     * Singleton var
     * @var App\LXC\LXC
     */
    protected static $instance;

    protected $config;
    protected $indices;

    // protected $templates;

    /**
     * Construct LXC API
     * @param array $config Configuration
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Singleton getter
     * @return App\LXC\LXC The singleton
     */
    public static function getInstance() {
        if (is_null(static::$instance)) {
            $app = \Bono\App::getInstance();
            $config = $app->config('lxc');
            static::$instance = new LXC($config);
        }

        return static::$instance;
    }

    /**
     * Is container with name exists?
     * @param  string $name Container name
     * @return bool         is exists?
     */
    public function exists($name) {
        $indices = $this->getIndices(true);
        return array_key_exists($name, $indices);
    }

    /**
     * Get indices of all available containers.
     *
     * @return array
     */
    public function getIndices() {
        if (is_null($this->indices)) {
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

    /**
     * Find all container
     * @return array Array of containers found
     */
    public function find() {
        $indices = $this->getIndices();
        $containers = array();
        foreach ($indices as $index) {
            $containers[$index] = $this->findOne($index);
        }

        return $containers;
    }

    /**
     * Find container with name
     * @param  string $name The container name
     * @return array        Information about container
     */
    public function findOne($name) {
        if ($this->exists($name)) {

            $info = $this->fetchInfo($name);
            if (is_null($info)) {
                return NULL;
            }
            $info['config'] = $this->fetchConfig($name);

            // $info['ip_address'] = $this->getIPAddress($name);
            // $info['reserved_ip'] = $this->getIPAddress($name, false);

            return $info;
        }
    }

    /**
     * Fetch configuration of container
     * @param  string $name The container name
     * @return array       configuration
     */
    public function fetchConfig($name) {

        $configFile = $this->config['directory'].'/'.$name.'/config';
        $content = explode("\n", file_get_contents($configFile));
        $config = array();
        foreach ($content as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') continue;

            $token = explode('=', $line, 2);
            $key = trim($token[0]);
            $value = trim($token[1]);

            if (!isset($config[$key])) {
                $config[$key] = $value;
            } elseif (!is_array($config[$key])) {
                $a = $config[$key];
                $config[$key] = array();
                $config[$key][] = $a;
                $config[$key][] = $value;
            } else {
                $config[$key][] = $value;
            }
        }

        return $config;
    }

    public function storeConfig($name, $config) {
        $this->repairConfig($name, $config);

        $tmpFile = tempnam(getcwd().'/../tmp', 't');

        $f = fopen($tmpFile, 'w');

        foreach ($config as $key => $token) {
            if (is_array($token)) {
                foreach ($token as $t) {
                    $line = $key.' = '.$t."\n";
                    fputs($f, $line);
                }
            } else {
                $line = $key.' = '.$token."\n";
                fputs($f, $line);
            }
        }
        fclose($f);

        $configFile = $this->config['directory'].'/'.$name.'/config';
        exec(sprintf('sudo '.realpath(getcwd().'/../bin/luthor-copy').' "%s" "%s"', $tmpFile, $configFile), $result, $errCode);

        unlink($tmpFile);

    }

    public function repairConfig($name, $config) {
        $content = "
auto lo
iface lo inet loopback
";
        if (isset($config[static::$KEY['IP_ADDRESS']])) {
            $content .= "
auto eth0
iface eth0 inet static
    address {$config[static::$KEY['IP_ADDRESS']]}
    netmask {$config[static::$KEY['NETWORK_NETMASK']]}
    gateway {$config[static::$KEY['NETWORK_GATEWAY']]}
";
        } else {
            $content .= "
auto eth0
iface eth0 inet dhcp
";
        }

        $netFile = $this->config['directory'].'/'.$name.'/rootfs/etc/network/interfaces';
        $tmpFile = tempnam(getcwd().'/../tmp', 't');
        file_put_contents($tmpFile, $content);
        exec(sprintf('sudo '.realpath(getcwd().'/../bin/luthor-copy').' "%s" "%s"', $tmpFile, $netFile), $result, $errCode);
        unlink($tmpFile);

        $pokeFile = $this->config['directory'].'/'.$name.'/rootfs/etc/network/if-up.d/luthor';
        if (!file_exists($pokeFile)) {
            var_dump($config);
            $https = empty($this->config['luthor.https']) ? 'http' : 'https';

            $tmpFile = tempnam(getcwd().'/../tmp', 't');

            $content = "#!/bin/sh
date +\"%F%T\" >> /var/log/luthor.log
which curl >> /var/log/luthor.log
url=\"{$https}://{$config[static::$KEY['NETWORK_REPORT']]}{$this->config['luthor.path']}/container/{$config[static::$KEY['ID']]}/poke\"
echo \$url >> /var/log/luthor.log
curl \"\$url\" >> /var/log/luthor.log
";

            file_put_contents($tmpFile, $content);

            exec(sprintf('sudo '.realpath(getcwd().'/../bin/luthor-copy').' "%s" "%s" "+rx"', $tmpFile, $pokeFile), $result, $errCode);

            unlink($tmpFile);
        }


    }

    /**
     * Fetch info from cli
     * @param  string $name The container name
     * @return array        Information about container
     */
    public function fetchInfo($name) {
        exec(sprintf('sudo lxc-info -qn "%s"', $name), $result, $errCode);
        var_dump($result);
        exit;
        $info = NULL;
        if (!$errCode && $result) {
            $state = explode(':', $result[0]);
            $pid = explode(':', $result[1]);

            $info = array(
                'name' => $name,
                'state' => static::$STATES[trim($state[1])],
                'pid' => ($pid[1] == -1) ? NULL : (int) $pid[1],
            );
        }
        return $info;
    }

    /**
     * Start container
     * @param  string $name The container name
     */
    public function start($name) {
        exec(sprintf('sudo lxc-start -dn %s', $name), $result, $errCode);
        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }

        $info = null;
        for($i = 0; $i < 20; $i++) {
            $info = $this->findOne($name);
            if ($info['state'] != 0) {
                return $info;
            }
            sleep(1);
        }

        throw new \Exception('Wrong state or something wrong happened in the middle of process');
    }

    /**
     * Stop container
     * @param  string $name The container name
     */
    public function stop($name) {
        exec(sprintf('sudo lxc-stop -n %s', $name), $result, $errCode);
        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }

        $info = null;
        for($i = 0; $i < 20; $i++) {
            $info = $this->findOne($name);
            if ($info['state'] == 0) {
                return $info;
            }
            sleep(1);
        }

        throw new \Exception('Wrong state or something wrong happened in the middle of process');
    }

    /**
     * Fetch memory usage from cgroup
     * @param  string $name The container name
     * @return int          Memory usage
     */
    public function fetchMemUsage($name) {
        exec(sprintf('sudo lxc-cgroup -n %s memory.usage_in_bytes', $name), $result, $errCode);
        if ($result) {
            return (int) $result[0];
        }
    }

    public function chpasswd($id, $password) {
        exec(sprintf("sudo ".realpath(getcwd().'/../bin/luthor-chpasswd').'"%s" "%s" "%s"', $this->config['directory'], $id, $password), $result, $errCode);
    }

    public function create($options) {
        $name = $options['name'];
        $template = $options['template'];

        if ($this->exists($name)) {
            throw new \Exception(sprintf('Container %s already exists', $name));
        }

        $arg = NULL;

        if (!empty($options['network_object'])) {
            if (is_null($arg)) $arg = ' --';
            $arg .= ' --link '.$options['network_object']['bridge'];
        }

        if (!empty($options['reserved_ip'])) {
            if (is_null($arg)) $arg = ' --';
            $arg .= ' --ip '.$options['reserved_ip'];
        }

        exec(sprintf('sudo lxc-create -n "%s" -t "%s" %s', $name, $template, $arg), $result, $errCode);
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

    public function destroy($name) {
        exec(sprintf('sudo lxc-destroy -n "%s"', $name), $result, $errCode);
        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }
    }

    public function save($id, $info) {
        $toSave = array();
        foreach ($info as $key => $value) {
            if (strpos($key, '.') !== false) {
                $toSave[$key] = $value;
            }
        }

        $tmpFile = tempnam(getcwd().'/../tmp', 't');

        $f = fopen($tmpFile, 'w');
        foreach ($toSave as $key => $value) {
            fputs($f, $key.' = '.$value."\n");
        }
        fclose($f);

        exec(sprintf('sudo '.realpath(getcwd().'/../bin/luthor-copy').' "%s" "%s"', $tmpFile, $this->config['directory'].'/'.$id.'/config'), $result, $errCode);

        unlink($tmpFile);

        return 1;
    }

}