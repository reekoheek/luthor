<?php

/**
 * Luthor - ContainerList Administration
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
class ContainerList
{

    /**
     * Singleton var
     * @var App\LXC\ContainerList
     */
    protected static $instance;

    protected $config;
    protected $indices;
    protected $cache;

    // protected $templates;

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

    /**
     * Construct LXC API
     * @param array $config Configuration
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->resetCache();
    }

    public function resetCache()
    {
        $this->cache = array();
    }

    public function newInstance()
    {
        return new Container(null, $this->config);
    }

    /**
     * Is container with name exists?
     * @param  string $name Container name
     * @return bool         is exists?
     */
    public function exists($name)
    {
        $indices = $this->getIndices();
        return array_key_exists($name, $indices);
    }

    /**
     * Get indices of all available containers.
     *
     * @return array
     */
    public function getIndices()
    {
        if (is_null($this->indices)) {
            $this->indices = array();

            exec(sprintf('sudo lxc-ls -1'), $result, $errCode);
            if (!empty($result)) {
                foreach ($result as $entry) {
                    $this->indices[$entry] = $entry;
                }
            }
        }
        return $this->indices;
    }

    /**
     * Find all container
     * @return array Array of containers found
     */
    public function find()
    {
        if (!empty($this->cache)) {
            return $this->cache;
        }

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
    public function findOne($name)
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $info = null;

        if ($this->exists($name)) {
            $lxcInfoCmd = 'sudo lxc-info -qn "%s" -H ';
            exec(sprintf($lxcInfoCmd, $name), $result, $errCode);

            if (!$errCode && $result) {
                $info = new Container(null, $this->config);
                foreach ($result as $line) {
                    $line = explode(':', $line);
                    $line[0] = strtolower(str_replace(' ', '_', trim($line[0])));
                    $line[1] = trim($line[1]);
                    $info[$line[0]] = $line[1];
                }

                $fetchConfigCmd = $lxcInfoCmd.'-c "%s"';

                $result = '';
                $errCode = 0;
                exec(sprintf($fetchConfigCmd, $name, 'lxc.cgroup.memory.limit_in_bytes'), $result, $errCode);
                $info['memlimit'] = trim($result[0]) ?: null;

                $result = '';
                $errCode = 0;
                exec(sprintf($fetchConfigCmd, $name, 'lxc.cgroup.memory.memsw.limit_in_bytes'), $result, $errCode);
                $info['memswlimit'] = trim($result[0]) ?: null;

                $result = '';
                $errCode = 0;
                exec(sprintf($fetchConfigCmd, $name, 'lxc.cgroup.cpu.cpus'), $result, $errCode);
                $info['cpus'] = trim($result[0]) ?: null;

                $result = '';
                $errCode = 0;
                exec(sprintf($fetchConfigCmd, $name, 'lxc.cgroup.cpu.shares'), $result, $errCode);
                $info['cpu_shares'] = trim($result[0]) ?: null;

                $networks = array();
                for ($i=0; $i < 5; $i++) {
                    $errCode = 0;
                    $result = '';
                    exec(sprintf($fetchConfigCmd, $name, 'lxc.network.'.$i.'.type'), $result, $errCode);

                    if (empty($result)) {
                        // if there is no network for index and it is first index then override to empty network
                        // otherwise it would be no more network after that
                        if ($i == 0) {
                            $result[0] = 'empty';
                        } else {
                            break;
                        }
                    }

                    if ($result[0] === 'none' || $result[0] === 'empty') {
                        $network = array(
                            'type' => $result[0],
                        );
                        $networks = array( $network );
                        break;
                    }

                    $network = array(
                        'type' => $result[0],
                    );

                    $result = '';
                    exec(sprintf($fetchConfigCmd, $name, 'lxc.network.'.$i.'.flags'), $result, $errCode);
                    if (!empty($result) && $result[0] !== '') {
                        $network['flags'] = $result[0];
                    }

                    $result = '';
                    exec(sprintf($fetchConfigCmd, $name, 'lxc.network.'.$i.'.link'), $result, $errCode);
                    if (!empty($result) && $result[0] !== '') {
                        $network['link'] = $result[0];
                    }

                    $result = '';
                    exec(sprintf($fetchConfigCmd, $name, 'lxc.network.'.$i.'.name'), $result, $errCode);
                    if (!empty($result) && $result[0] !== '') {
                        $network['name'] = $result[0];
                    }

                    $result = '';
                    exec(sprintf($fetchConfigCmd, $name, 'lxc.network.'.$i.'.hwaddr'), $result, $errCode);
                    if (!empty($result) && $result[0] !== '') {
                        $network['hwaddr'] = $result[0];
                    }

                    $result = '';
                    exec(sprintf($fetchConfigCmd, $name, 'lxc.network.'.$i.'.ipv4'), $result, $errCode);
                    if (!empty($result) && $result[0] !== '') {
                        $network['ipv4'] = $result[0];
                    }

                    $result = '';
                    exec(sprintf($fetchConfigCmd, $name, 'lxc.network.'.$i.'.ipv4.gateway'), $result, $errCode);
                    if (!empty($result) && $result[0] !== '') {
                        $network['ipv4_gateway'] = $result[0];
                    }

                    $networks[] = $network;
                }

                $info['networks'] = $networks;
            }

        }

        $this->cache[$name] = $info;

        return $info;
    }
}
