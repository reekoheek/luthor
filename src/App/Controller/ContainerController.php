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

use App\LXC\ContainerList;
use App\LXC\TemplateList;
use Bono\Helper\URL;
use Norm\Norm;
use Norm\Filter\Filter;
use Norm\Filter\FilterException;
use Norm\Controller\NormController;

class ContainerController extends NormController
{
    protected $containers;
    // protected $templates;

    public function __construct($app, $name)
    {
        parent::__construct($app, $name);

        $this->map('/null/populate', 'populate')->via('GET');
        $this->map('/:id/start', 'start')->via('GET');
        $this->map('/:id/stop', 'stop')->via('GET');
        $this->map('/:id/network', 'network')->via('GET');
        $this->map('/:id/network', 'networkAdd')->via('POST');
        $this->map('/:id/network/:index/remove', 'networkRemove')->via('GET');
        $this->map('/:id/attach', 'attach')->via('GET');
        $this->map('/:id/chpasswd', 'chpasswd')->via('GET', 'POST');
        // $this->map('/:id/poke', 'poke')->via('GET');

        $this->containers = ContainerList::getInstance();
        // $this->templates = TemplateList::getInstance();
    }

    public function attach($id)
    {
        $model = $this->collection->findOne($id);

        if (is_null($model)) {
            $this->app->notFound();
            return;
        }

        try {
            $this->data['entry'] = $model;
            $container = $this->containers->findOne($model['name']);
            if ($cmd = $this->request->get('cmd')) {
                $result = $container->attach($cmd);
                $this->data['result'] = $result;
            }
        } catch (\Exception $e) {
            h('notification.error', $e);
        }
    }

    public function populate()
    {
        try {
            $this->collection->remove();
            $entries = $this->containers->find();

            foreach ($entries as $entry) {
                $model = $this->collection->findOne(array('name' => $entry['name']));
                if (!$model) {
                    $model = $this->collection->newInstance();
                }
                $model->set($entry->toArray());
                $model->save();
            }
            h('notification.info', 'Existing containers populated.');

        } catch (\Exception $e) {
            h('notification.error', $e);
        }

        $this->redirect($this->getBaseUri());
    }

    public function network($id)
    {
        $model = $this->collection->findOne($id);

        if (is_null($model)) {
            $this->app->notFound();
            return;
        }

        try {
            $this->data['entry'] = $model;
        } catch (\Exception $e) {
            h('notification.error', $e);
        }
    }

    public function networkAdd($id)
    {
        $model = $this->collection->findOne($id);

        if (is_null($model)) {
            $this->app->notFound();
            return;
        }

        try {
            $post = array();
            foreach ($this->request->post() as $k => $v) {
                if (empty($v)) {
                    continue;
                }

                $post[$k] = $v;
            }
            if ($post['type'] === 'none' || $post['type'] === 'empty') {
                $post = array('type' => $post['type']);
            }

            if ($post['type'] === 'none' || $post['type'] === 'empty' ||
                $model['networks'][0]['type'] === 'empty' || $model['networks'][0]['type'] === 'none') {
                $networks = array(
                    $post
                );
                $model['networks'] = $networks;
            } else {
                if (is_null($model['networks'])) {
                    $model['networks'] = array();
                }
                $model['networks']->add($post);
            }
            $model->save();

            h('notification.info', 'Container network added.');
        } catch (\Exception $e) {
            h('notification.error', $e);
        }

        $this->redirect(\URL::site($this->getBaseUri().'/'.$id.'/network'));
    }

    public function networkRemove($id, $networkIndex)
    {
        $model = $this->collection->findOne($id);

        if (is_null($model)) {
            $this->app->notFound();
            return;
        }

        try {
            $networks = array();
            foreach ($model['networks'] as $index => $network) {
                if ($index == $networkIndex) {
                    continue;
                }
                $networks[] = $network;
            }

            if (empty($networks)) {
                $networks[] = array(
                    'type' => 'empty'
                );
            }

            $model['networks'] = $networks;

            $model->save();

            h('notification.info', 'Container network removed.');
        } catch (\Exception $e) {
            h('notification.error', $e);
        }

        $this->redirect(\URL::site($this->getBaseUri().'/'.$id.'/network'));
    }

    public function start($id)
    {
        $model = $this->collection->findOne($id);

        if (is_null($model)) {
            $this->app->notFound();
            return;
        }

        try {
            $container = $this->containers->findOne($model['name']);
            $container->start();

            $container = $this->containers->findOne($model['name']);
            $model->set($container->toArray());
            $model->save();

            h('notification.info', 'Container started.');

        } catch (\Exception $e) {
            h('notification.error', $e);
        }

        $this->redirect($this->getBaseUri());
    }

    public function stop($id)
    {
        $model = $this->collection->findOne($id);

        if (is_null($model)) {
            $this->app->notFound();
            return;
        }

        try {
            $container = $this->containers->findOne($model['name']);
            $container->stop();

            $container = $this->containers->findOne($model['name']);
            $model->set($container->toArray());
            $model->save();

            h('notification.info', 'Container stopped.');

        } catch (\Exception $e) {
            h('notification.error', $e);
        }

        $this->redirect($this->getBaseUri());
    }

    public function chpasswd($id)
    {
        $model = $this->collection->findOne($id);

        if (is_null($model)) {
            $this->app->notFound();
            return;
        }


        if ($this->request->isPost()) {
            $post = $this->request->post();
            try {
                $filter = Filter::create(array(
                    'password' => 'trim|required|confirmed',
                ));
                $post = $filter->run($post);
                $errors = $filter->errors();

                if ($errors) {
                    $e = new FilterException();
                    $e->sub($errors);
                    throw $e;
                }

                $model = $this->collection->findOne($id);
                $container = $this->containers->findOne($model['name']);
                $container->chpasswd($post['password']);

                h('notification.info', 'Password changed.');

                // $this->redirect($this->getBaseUri());
            } catch (\Exception $e) {
                h('notification.error', $e);
            }
        }

        $this->data['entry'] = $model;
    }
}
