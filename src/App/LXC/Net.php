<?php

namespace App\LXC;

class Net {

    public function findOne($name) {
        $entry = array(
            'name' => $name,
        );
        $result = '';
        $this->exec('net-info', array($entry['name']), $result);
        foreach ($result as $key => $value) {
            if ($value) {
                $key = strtolower(trim(trim(substr($value, 0, 16)), ':'));
                $value = substr($value, 16);
                $entry[$key] = $value;
            }
        }


        $entry['state'] = ($entry['active'] === 'yes') ? 1 : 0;
        $entry['autostart'] = ($entry['autostart'] === 'yes') ? 1 : 0;
        $entry['persistent'] = ($entry['persistent'] === 'yes') ? 1 : 0;
        unset($entry['active']);

        $result = '';
        $this->exec('net-dumpxml', array($entry['name']), $result);
        foreach ($result as $value) {
            if (strpos($value, '<ip address') !== false) {
                $value = explode("'", trim($value));
                $entry['ip_address'] = $value[1];
                $entry['netmask'] = $value[3];
            } elseif (strpos($value, '<range') !== false) {
                $value = explode("'", trim($value));
                $entry['dhcp_start'] = $value[1];
                $entry['dhcp_end'] = $value[3];
            }
        }
        return $entry;
    }

    public function find() {
        $result = '';
        $this->exec('net-list', array('--all'), $result);
        next($result);
        next($result);

        $entries = array();
        while(list($key, $value) = each($result)) {
            if ($value) {
                $row = preg_split('/[\\s]+/', $value);

                $entry = $this->findOne($row[0]);
                $entries[] = $entry;
            }
        }

        // var_dump($entries);
        // exit;

        return $entries;
    }

    public function destroy($name) {
        $result = '';
        return $this->exec('net-undefine '.$name, array(), $result);
    }

    protected function exec($command, $args, &$result) {
        $errCode = 0;
        exec(sprintf('sudo virsh -c lxc:/// %s ', $command).implode(' ', $args), $result, $errCode);
        return $errCode;
    }

}