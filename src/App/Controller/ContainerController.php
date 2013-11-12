<?php

namespace App\Controller;

use Norm\Norm;
use App\LXC\LXC;
use Bono\Helper\URL;
use Bono\Controller\RestController;

class ContainerController extends RestController {
    public function __construct($app, $name) {
        parent::__construct($app, $name);

        $app->get($this->getBaseUri().'/:id/onoff', array($this, 'delegate'))->name('onoff');
        $app->get($this->getBaseUri().'/:id/poke', array($this, 'delegate'))->name('poke');

        $this->lxc = new LXC($app->config('lxc'));

        $schema = $this->app->config('norm.schemas');
        $this->data['_schema'] = $schema['Container'];

        $this->data['_templates'] = $this->lxc->getTemplates();
    }

    public function search() {

        $toggleOnOff = function($entry) {
            if ($entry['state'] === 0) {
                return 'Start';
            } else {
                return 'Stop';
            }
        };

        $this->data['_actions'] = array(
            'start' => array('label' => $toggleOnOff, 'url' => $this->getBaseUri().'/%s/onoff'),
            'update' => array('label' => 'Update', 'url' => $this->getBaseUri().'/%s/update'),
            'delete' => array('label' => 'Delete', 'url' => $this->getBaseUri().'/%s/delete'),
        );

        $this->data['entries'] = $this->lxc->find();
    }

    public function create() {
        if ($this->request->isPost()) {
            try {
                $this->lxc->create($this->data['entry']['name'], $this->data['entry']['template']);
            } catch(\Exception $e) {
                return $this->flashNow('error', $e->getMessage());
            }

            $this->flash('info', 'Container created.');
            $this->redirect($this->getBaseUri());
        }
    }

    public function read($id) {
        $this->data['entry'] = $this->lxc->getInfo($id);

        if (is_null($this->data['entry'])) {
            $this->app->notFound();
        }
    }

    public function update($id) {
        if ($this->request->isPost()) {
            $this->flash('info', 'Container updated.');
            $this->redirect($this->getBaseUri());
        } else {
            $this->data['entry'] = $this->lxc->getInfo($id);

            if (is_null($this->data['entry'])) {
                $this->app->notFound();
            }
        }
    }

    public function delete($id) {
        if ($this->request->isPost()) {
            try {
                $info = $this->lxc->getInfo($id);
                if ($info['state'] !== 0) {
                    throw new \Exception('Container is running, stop the container first to delete.');
                }
                $this->lxc->destroy($id);
            } catch(\Exception $e) {
                return $this->flashNow('error', $e->getMessage());
            }

            $this->flash('info', 'Container deleted.');
            $this->redirect($this->getBaseUri());
        } else {
            $this->data['entry'] = $this->lxc->getInfo($id);

            if (is_null($this->data['entry'])) {
                $this->app->notFound();
            }


        }
    }

    // protected function ipCIDRCheck ($IP, $CIDR) {
    //     list ($net, $mask) = explode ("/", $CIDR);

    //     $ip_net = ip2long ($net);
    //     $ip_mask = ~((1 << (32 - $mask)) - 1);

    //     $ip_ip = ip2long ($IP);

    //     $ip_ip_net = $ip_ip & $ip_mask;

    //     return ($ip_ip_net == $ip_net);
    // }

    // public function poke($id) {
    //     $this->response->template('');
    //     $allowed = $this->app->config('lxc');
    //     $allowed = $this->ipCIDRCheck($_SERVER['REMOTE_ADDR'], $allowed['luthor.allowed']);
    //     if (!$allowed) {
    //         $this->app->notFound();
    //     }

    //     $info = $this->lxc->getInfo($id);
    //     if (empty($info)) {
    //         $this->app->notFound();
    //     } else {
    //         $info['luthor.ip_address'] = $_SERVER['REMOTE_ADDR'];
    //         $this->lxc->save($id, $info);
    //     }
    // }

    public function onoff($id) {
        $info = $this->lxc->getInfo($id);

        if (is_null($info)) {
            $this->app->notFound();
            return;
        }

        try {
            if ($info['state'] === 0) {
                $this->lxc->start($id);
                $this->flash('info', 'Container started.');
            } else {
                $this->lxc->stop($id);
                $this->flash('info', 'Container stopped.');
            }
        } catch(\Exception $e) {
            return $this->flashNow('error', $e->getMessage());
        }
        sleep(1);
        $this->redirect($this->getBaseUri());
    }
}