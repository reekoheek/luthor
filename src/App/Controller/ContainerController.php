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
namespace App\Controller;

use App\LXC\LXC;
use App\LXC\Template;
use Bono\Helper\URL;
use Norm\Norm;
use Norm\Filter\Filter;
use Norm\Controller\NormController;

class ContainerController extends NormController {
    public function __construct($app, $name) {
        parent::__construct($app, $name);

        $this->map('/null/populate', 'populate')->via('GET');
        $this->map('/:id/onoff', 'onoff')->via('GET');
        $this->map('/:id/chpasswd', 'chpasswd')->via('GET', 'POST');
        $this->map('/:id/poke', 'poke')->via('GET');

        $this->lxc = LXC::getInstance();
        $this->lxcTemplate = new Template($this->app->config('lxc'));
    }

    public function chpasswd($id) {
        if ($this->request->isPost()) {
            $filter = new Filter(array(
                        'password' => array(
                            'trim', 'required', 'confirmed',
                            ),
                        ));
            $filter->run($this->data['entry']);
            $errors = $filter->errors();

            if ($errors) {
                return $this->flashNow('error', (new \Norm\Filter\FilterException())->sub($errors)->__toString());
            }

            $model = $this->collection->findOne($id);

            $this->lxc->chpasswd($model['name'], $this->data['entry']['password']);

            $this->flash('info', 'Password changed.');
            $this->redirect($this->getBaseUri());
        }
    }

    public function create() {
        $templates = Norm::factory('Template')->find(array('luthor_version' => 'v1'));
        $templateData = array();
        foreach ($templates as $key => $template) {
            $templateData[$template['name']] = $template['name'];
        }

        $this->set('_templates', $templateData);

        if ($this->request->isPost()) {
            try {
                $post = $this->request->post();
                $filter = Filter::fromSchema($this->collection->schema(), array(
                            'template' => 'required',
                            ));
                $filter->run($post);
                $errors = $filter->errors();

                if ($errors) {
                    throw new \Exception((new \Norm\Filter\FilterException())->sub($errors)->__toString());
                }

                $entry = array();
                foreach ($post as $key => $value) {
                    $entry[$key] = $value;
                }
                $entry['network_object'] = Norm::factory('Network')->findOne($entry['network']);
                $entry = $this->lxc->create($entry);
                $this->_populate($entry);
            } catch(\Exception $e) {
                var_dump($e);
                exit;
                return $this->flashNow('error', $e->getMessage());
            }

            $this->flash('info', 'Container created.');
            $this->redirect($this->getBaseUri());
        }
    }

    public function update($id) {
        if ($this->request->isPost() || $this->request->isPut()) {
            $post = $this->request->post();
            $info = $this->lxc->findOne($post['name']);

            $info['config'][LXC::$KEY['ID']] = $id;

            $network = Norm::factory('Network')->findOne($post['network']);
            $info['config'][LXC::$KEY['NETWORK_LINK']] = $network['bridge'];
            $info['config'][LXC::$KEY['NETWORK_IPV4']] = '0.0.0.0';
            $info['config'][LXC::$KEY['NETWORK_REPORT']] = $network['report'];
            $info['config'][LXC::$KEY['NETWORK_NETMASK']] = $network['netmask'];
            $info['config'][LXC::$KEY['NETWORK_GATEWAY']] = $network['ip_address'];

            if ((empty($post['ip_address']))) {
                unset($info['config'][LXC::$KEY['IP_ADDRESS']]);
            } else {
                $info['config'][LXC::$KEY['IP_ADDRESS']] = $post['ip_address'];
            }
            if ((empty($post['memlimit']))) {
                unset($info['config'][LXC::$KEY['MEM_LIMIT']]);
            } else {
                $info['config'][LXC::$KEY['MEM_LIMIT']] = $post['memlimit'];
            }
            if ((empty($post['memswlimit']))) {
                unset($info['config'][LXC::$KEY['MEMSW_LIMIT']]);
            } else {
                $info['config'][LXC::$KEY['MEMSW_LIMIT']] = $post['memswlimit'];
            }
            if ((empty($post['cpus']))) {
                unset($info['config'][LXC::$KEY['CPUS']]);
            } else {
                $info['config'][LXC::$KEY['CPUS']] = $post['cpus'];
            }
            if ((empty($post['cpu_shares']))) {
                unset($info['config'][LXC::$KEY['CPU_SHARES']]);
            } else {
                $info['config'][LXC::$KEY['CPU_SHARES']] = $post['cpu_shares'];
            }

            $this->lxc->storeConfig($post['name'], $info['config']);

            $config = $this->lxc->fetchConfig($post['name']);
            $a = $this->app->environment['slim.request.form_hash'];
            $a['config'] = $config;
            $this->app->environment['slim.request.form_hash'] = $a;
        }
        return parent::update($id);
    }

    public function delete($id) {
        $model = $this->collection->findOne($id);
        if (is_null($model)) {
            $this->app->notFound();
        }

        if ($this->request->isPost()) {
            try {
                if ($model['state'] != 0) {
                    throw new \Exception('Container is running, stop the container first to delete.');
                }

                if (!is_null($this->lxc->findOne($model['name']))) {
                    $this->lxc->destroy($model['name']);
                }

                $model->remove();
            } catch(\Exception $e) {
                return $this->flashNow('error', $e->getMessage());
            }

            $this->flash('info', 'Container deleted.');
            $this->redirect($this->getBaseUri());
        }
    }

    public function onoff($id) {
        $model = $this->collection->findOne($id);

        if (is_null($model)) {
            $this->app->notFound();
            return;
        }

        try {
            $model['actual_ip_address'] = '';
            $model->save();

            if ($model['state'] == 0) {
                $info = $this->lxc->start($model['name']);
                $this->flash('info', 'Container started.');
            } else {
                $info = $this->lxc->stop($model['name']);
                $this->flash('info', 'Container stopped.');
            }
            $this->_populate($info);
        } catch(\Exception $e) {
            $this->flash('error', $e->getMessage());
        }
        $this->redirect($this->getBaseUri());
    }

    public function poke($id) {
        $model = $this->collection->findOne($id);
        $model['actual_ip_address'] = $_SERVER['REMOTE_ADDR'];
        $model->save();
        exit;
    }

    public function populate() {
        try {
            $entries = $this->lxc->find();
            foreach ($entries as $entry) {
                $this->_populate($entry);
            }
        } catch(\Exception $e) {
            echo $e;
            exit;
        }
        $this->flash('info', 'Container populated.');
        $this->redirect($this->getBaseUri());
    }

    protected function _populate($entry) {
        $model = $this->collection->findOne(array('name' => $entry['name']));
        if (is_null($model)) {
            $model = $this->collection->newInstance();
        }

        $model->set($entry);

        if (isset($entry['config']['lxc.network.link'])) {
            $network = Norm::factory('Network')->findOne(array('bridge' => $entry['config']['lxc.network.link']));
            $model['network'] = $network;
        }
        $model['ip_address'] = @$entry['config'][LXC::$KEY['IP_ADDRESS']];
        $model['memlimit'] = @$entry['config'][LXC::$KEY['MEM_LIMIT']];
        $model['memswlimit'] = @$entry['config'][LXC::$KEY['MEMSW_LIMIT']];
        $model['cpus'] = @$entry['config'][LXC::$KEY['CPUS']];
        $model['cpu_shares'] = @$entry['config'][LXC::$KEY['CPU_SHARES']];

        $model->save();
    }
}
