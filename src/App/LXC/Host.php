<?php

namespace App\LXC;

class Host
{
    public function getUptime()
    {
        $uptime = explode(' ', exec('cat /proc/uptime'));
        $uptime['up'] = $uptime[0];
        $uptime['idle'] = $uptime[1];
        unset($uptime[0]);
        unset($uptime[1]);
        foreach ($uptime as $key => &$value) {
            $value = (double) $value;
        }
        return $uptime;
    }

    public function getCPU()
    {
        $result = '';
        exec('cat /proc/stat | grep cpu', $result);

        $cpus = array();
        foreach ($result as $key => $line) {
            $line = preg_split('/\\s+/', $line);

            $cpu = array(
                'name' => $line[0],
                'user' => (int) $line[1],
                'nice' => (int) $line[2],
                'system' => (int) $line[3],
                'idle' => (int) $line[4],
                'iowait' => (int) $line[5],
                'irq' => (int) $line[6],
                'softirq' => (int) $line[7],
            );
            $cpu['all'] = $cpu['user'] + $cpu['nice'] + $cpu['system'] +
                $cpu['idle'] + $cpu['iowait'] + $cpu['irq'] + $cpu['softirq'];
            $cpu['free'] = $cpu['idle'] / $cpu['all'];
            $cpu['usage'] = 1 - $cpu['free'];
            $cpus[$cpu['name']] = $cpu;
        }

        return $cpus;
    }

    public function getMem()
    {
        $result = '';
        exec('cat /proc/meminfo', $result);
        $mem = array();
        foreach ($result as $key => $value) {
            $value = explode(':', $value);
            $mem[trim($value[0])] = trim($value[1]);
        }

        $mem['total'] = (int) $mem['MemTotal'];
        $mem['free'] = (int) $mem['MemFree'];
        $mem['buffers'] = (int) $mem['Buffers'];
        $mem['cached'] = (int) $mem['Cached'];
        $mem['used'] = ($mem['total'] - ($mem['free'] + $mem['buffers'] + $mem['cached']));

        return $mem;
    }

    public function getDisk()
    {
        $result = exec('df -h /');
        $result = preg_split('/\\s+/', $result);

        $disk = array(
            'fs' => $result[0],
            'size' => $result[1],
            'used' => $result[2],
            'available' => $result[3],
            'percent' => $result[4],
            'mount' => $result[5],
        );
        return $disk;
    }

    public function getInfo()
    {
        $entry = array();

        $entry['uptime'] = $this->getUptime();
        $entry['cpus'] = $this->getCPU();
        $entry['mem'] = $this->getMem();
        $entry['disk'] = $this->getDisk();

        return $entry;
    }
}
