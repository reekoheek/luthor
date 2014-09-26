<?php

namespace App\LXC;

use \Norm\Filter\FilterException;

class Container extends Object
{
    protected $attributes = array();
    protected $options = array();

    public function __construct($attributes = array(), $options = array())
    {
        $defaultAttributes = array(
            'name' => null,
            'state' => null,
            'pid' => null,
            'ip' => null,
            'cpu_use' => null,
            'blkio_use' => null,
            'memory_use' => null,
            'link' => null,
            'tx_bytes' => null,
            'rx_bytes' => null,
            'total_bytes' => null,
            'memlimit' => null,
            'memswlimit' => null,
            'cpus' => null,
            'cpu_shares' => null,
            'networks' => array(),
        );

        $attributes = array_merge($defaultAttributes, $attributes ?: array());

        parent::__construct($attributes, $options);
    }

    public function save()
    {
        $configFile = $this->options['directory'].'/'.$this->attributes['name'].'/config';

        if (!is_readable($configFile)) {

            $cmd = sprintf('sudo lxc-create -t "%s" -n "%s" 2>&1', $this['template'], $this['name']);
            $result = '';
            exec($cmd, $result, $errCode);

            if ($errCode) {
                $e = new FilterException();
                $e->sub(array(
                    'Something wrong happened in the middle of process',
                    '<pre>'.implode("\n", $result).'</pre>'
                ));
                throw $e;
            } else {
                h('notification.info', '<pre>'.implode("\n", $result).'</pre>');
            }
        }

        $orig = $config = file_get_contents($configFile);

        $config = preg_replace('/# Memory.+/', '', $config);
        $config = preg_replace('/# CPU.+/', '', $config);
        $config = preg_replace('/# Network.+/', '', $config);

        $config = preg_replace('/lxc.cgroup.memory.limit_in_bytes.+/', '', $config);
        if (!empty($this->attributes['memlimit'])) {
            $config .= "# Memory Limit\n";
            $config .= 'lxc.cgroup.memory.limit_in_bytes = '.$this->attributes['memlimit']."\n";
        }

        $config = preg_replace('/lxc.cgroup.memory.memsw.limit_in_bytes.+/', '', $config);
        if (!empty($this->attributes['memswlimit'])) {
            $config .= "# Memory and Swap Limit\n";
            $config .= 'lxc.cgroup.memory.memsw.limit_in_bytes = '.$this->attributes['memswlimit']."\n";
        }

        $config = preg_replace('/lxc.cgroup.cpu.cpus.+/', '', $config);
        if (!empty($this->attributes['cpus'])) {
            $config .= "# CPUS\n";
            $config .= 'lxc.cgroup.cpu.cpus = '.$this->attributes['cpus']."\n";
        }

        $config = preg_replace('/lxc.cgroup.cpu.shares.+/', '', $config);
        if (!empty($this->attributes['cpu_shares'])) {
            $config .= "# CPU Shares\n";
            $config .= 'lxc.cgroup.cpu.shares = '.$this->attributes['cpu_shares']."\n";
        }

        $config = preg_replace('/lxc.network.+/', '', $config);

        if (!empty($this->attributes['networks'])) {
            foreach ($this->attributes['networks'] as $i => $network) {
                $config .= "# Network configuration($i)\n";
                if (isset($network['type'])) {
                    $config .= "lxc.network.type = ${network['type']}\n";
                }

                if (isset($network['flags'])) {
                    $config .= "lxc.network.flags = ${network['flags']}\n";
                }

                if (isset($network['link'])) {
                    $config .= "lxc.network.link = ${network['link']}\n";
                }

                if (isset($network['name'])) {
                    $config .= "lxc.network.name = ${network['name']}\n";
                }

                if (isset($network['hwaddr'])) {
                    $config .= "lxc.network.hwaddr = ${network['hwaddr']}\n";
                }

                if (isset($network['ipv4'])) {
                    $config .= "lxc.network.ipv4 = ${network['ipv4']}\n";
                }

                if (isset($network['ipv4_gateway'])) {
                    $config .= "lxc.network.ipv4.gateway = ${network['ipv4_gateway']}\n";
                }
            }
        }

        $config = preg_replace("/\\n+/", "\n", $config);

        if ($config !== $orig) {
            $tmpFile = tempnam(getcwd().'/../tmp', 'lxconf-');

            file_put_contents($tmpFile, $config);

            exec(
                sprintf('sudo '.realpath(getcwd().'/../bin/luthor-copy').' "%s" "%s"', $tmpFile, $configFile),
                $result,
                $errCode
            );

            @unlink($tmpFile);

        }
    }

    public function destroy()
    {
        exec(sprintf('sudo lxc-destroy -n "%s"', $this['name']), $result, $errCode);
        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }
    }

    /**
     * Start container
     */
    public function start()
    {
        exec(sprintf('sudo lxc-start -dn %s', $this['name']), $result, $errCode);
        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }
    }

    /**
     * Stop container
     */
    public function stop()
    {
        exec(sprintf('sudo lxc-stop -n %s', $this['name']), $result, $errCode);
        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }
    }

    public function attach($cmd)
    {
        exec(sprintf("sudo lxc-attach -n %s -- '%s'", $this['name'], $cmd), $result, $errCode);
        return implode("\n", $result);
    }

    public function chpasswd($password)
    {
        $cmd = sprintf(
            'echo "root:%s" | sudo lxc-attach -n "%s" -- chpasswd',
            $password,
            $this['name']
        );
        exec($cmd, $result, $errCode);

        if ($errCode) {
            throw new \Exception('Something wrong happened in the middle of process');
        }
    }
}
