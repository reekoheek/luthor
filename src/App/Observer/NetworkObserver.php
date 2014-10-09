<?php

namespace App\Observer;

use App\LXC\NetworkList;

class NetworkObserver
{
    protected $networks;

    public function __construct()
    {
        $this->networks = NetworkList::getInstance();
    }

    public function saving($model)
    {
        $network = $this->networks->findOne($model['name']);
        if ($model->isNew() && is_null($network)) {
            $network = $this->networks->newInstance();
            $network['new'] = true;
            $network['name'] = $model['name'];
            $network['autostart'] = $model['autostart'];
            $network['bridge'] = $model['bridge'];
            $network['ip_address'] = $model['ip_address'];
            $network['netmask'] = $model['netmask'];
            $network['dhcp_start'] = $model['dhcp_start'];
            $network['dhcp_end'] = $model['dhcp_end'];
            $network->save();

            $model['uuid'] = $network['uuid'];
        }

        try {
            $network->setAutoStart($model['autostart']);
        } catch (\Exception $e) {
            $model['autostart'] = $model['autostart'] ? 0 : 1;
        }
    }

    public function removing($model)
    {
        $network = $this->networks->findOne($model->previous('name'));
        if ($network['state']) {
            throw new \Exception('Cannot delete active network');
        }
        $network->destroy();
    }
}
