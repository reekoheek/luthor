<?php

namespace App\Observer;

use App\LXC\TemplateList;

class TemplateObserver
{
    protected $templates;

    public function __construct()
    {
        $this->templates = TemplateList::getInstance();
    }

    public function saving($model)
    {
        $template = $this->templates->findOne($model['name']);
        if (is_null($template)) {
            $template = $this->templates->newInstance();
            $template['name'] = $model['name'];
        }

        $template['content'] = $model['content'];

        $template->save();

        $model['filename'] = $template['filename'];
    }

    public function removing($model)
    {
        $template = $this->templates->findOne($model->previous('name'));
        $template->destroy();
    }
}
