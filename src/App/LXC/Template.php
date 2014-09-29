<?php

namespace App\LXC;

class Template extends Object
{
    public function offsetSet($offset, $value)
    {
        if ($offset === 'name') {
            $filename = $this->options['templatesDirectory'].'/lxc-'.$value;
            parent::offsetSet('filename', $filename);
            $content = is_readable($filename) ? file_get_contents($filename) : '';
            parent::offsetSet('content', $content);
        }

        return parent::offsetSet($offset, $value);
    }

    public function save()
    {
        $orig = is_readable($this['filename']) ? file_get_contents($this['filename']) : '';
        $this['content'] = html_entity_decode($this['content']);
        if ($orig !== $this['content']) {
            $tmpFile = tempnam('../tmp', 't');

            file_put_contents($tmpFile, $this['content']);

            exec(
                sprintf(
                    'sudo '.realpath(getcwd().'/../bin/luthor-copy').' "%s" "%s" 0755',
                    $tmpFile,
                    $this['filename']
                ),
                $result,
                $errCode
            );

            @unlink($tmpFile);
        }
    }

    public function destroy()
    {
        exec(
            sprintf(
                'sudo '.realpath(getcwd().'/../bin/luthor-delete').' "%s"',
                $this['filename']
            )
        );
    }
}
