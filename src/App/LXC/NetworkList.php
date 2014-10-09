<?php

namespace App\LXC;

class NetworkList
{
    /**
     * Singleton var
     * @var App\LXC\NetworkList
     */
    protected static $instance;

    protected $config;

    /**
     * Singleton getter
     * @return App\LXC\NetworkList The singleton
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
     * Factory for new instance of network
     * @return App\LXC\Network
     */
    public function newInstance()
    {
        return new Network(null, $this->config);
    }

    /**
     * Construct LXC API
     * @param array $config Configuration
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    public function findOne($name)
    {
        $result = '';
        exec('sudo virsh -c lxc:/// net-info '.$name, $result, $errCode);

        if ($errCode) {
            return null;
        }

        $entry = new Network(array('name' => $name), $this->config);

        foreach ($result as $key => $value) {
            if ($value) {
                $value = preg_split('/\s+/', $value, 2);
                $value[0] = strtolower(trim($value[0]));
                $entry[trim($value[0], ' :')] = trim($value[1]);
            }
        }

        $entry['state'] = ($entry['active'] === 'yes') ? 1 : 0;
        $entry['autostart'] = ($entry['autostart'] === 'yes') ? 1 : 0;
        $entry['persistent'] = ($entry['persistent'] === 'yes') ? 1 : 0;

        unset($entry['active']);

        // $result = '';
        // exec('sudo virsh -c lxc:/// net-dumpxml '.$entry['name'], $result, $errCode);\
        // $entry['xml'] = implode("\n", $result);

        // foreach ($result as $value) {
        //     if (strpos($value, '<ip address') !== false) {
        //         $value = explode("'", trim($value));
        //         $entry['ip_address'] = $value[1];
        //         $entry['netmask'] = $value[3];
        //     } elseif (strpos($value, '<range') !== false) {
        //         $value = explode("'", trim($value));
        //         $entry['dhcp_start'] = $value[1];
        //         $entry['dhcp_end'] = $value[3];
        //     }
        // }
        return $entry;
    }

    public function find()
    {
        $result = '';
        exec('sudo virsh -c lxc:/// net-list --all', $result, $errCode);
        next($result);
        next($result);

        $entries = array();
        while (list($key, $value) = each($result)) {
            $value = trim($value);
            if ($value) {
                $row = preg_split('/[\\s]+/', $value);

                $entry = $this->findOne($row[0]);
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    public function destroy($name)
    {
        $result = '';
        return $this->exec('net-undefine '.$name, array(), $result);
    }
}
