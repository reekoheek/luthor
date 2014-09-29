<?php

namespace App\Observer;

use App\LXC\ContainerList;

class ContainerObserver
{
    protected $containers;

    public function __construct()
    {
        $this->containers = ContainerList::getInstance();
    }

    public function saving($model)
    {
        $prev = $model->previous();

        $container = $this->containers->findOne($model['name']);

        if (is_null($container)) {
            $container = $this->containers->newInstance();
            $container['name'] = $model['name'];
            if ($model->isNew()) {
                $container['template'] = $model['template'];
                $container['origin'] = $model['origin'];
            }
        }

        $container['memlimit'] = $model['memlimit'];
        $container['memswlimit'] = $model['memswlimit'];
        $container['cpus'] = $model['cpus'];
        $container['cpu_shares'] = $model['cpu_shares'];
        $container['networks'] = $model['networks'] ?: array();

        $container->save();
    }

    public function removing($model)
    {
        $container = $this->containers->findOne($model->previous('name'));
        $container->destroy();
    }
}
