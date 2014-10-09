<?php

namespace App\LXC;

class Network extends Object
{
    public function stop()
    {
        $result = '';
        exec("sudo virsh -c lxc:/// net-destroy {$this['name']}", $result, $errCode);
    }

    public function start()
    {
        $result = '';
        exec("sudo virsh -c lxc:/// net-start {$this['name']}", $result, $errCode);
    }

    public function setAutoStart($autostart) {
        $cmd = "sudo virsh -c lxc:/// net-autostart {$this['name']}";
        if (!$autostart) {
            $cmd .= ' --disable';
        }
        $result = '';
        exec($cmd, $result, $errCode);

        if ($errCode) {
            throw new \Exception(implode("\n", $result));
        }
    }

    public function save()
    {
        if ($this['new']) {
            $theme = \App::getInstance()->theme;

            $data = array(
                'entry' => $this,
            );
            $xml = $theme->partial('network/xml', $data);

            $tmpFile = tempnam(getcwd().'/../tmp', 'netxml-');

            file_put_contents($tmpFile, $xml);

            $result = '';
            exec("sudo virsh -c lxc:/// net-define '$tmpFile' 2>&1", $result, $errCode);

            @unlink($tmpFile);

            if ($errCode) {
                throw new \Exception($result[1]);
            }

            $result = '';
            exec("sudo virsh -c lxc:/// net-uuid {$this['name']}", $result, $errCode);

            if (!$errCode) {
                $this['uuid'] = $result[0];
            }
        }
    }

    public function destroy()
    {
        $result = '';
        exec("sudo virsh -c lxc:/// net-undefine {$this['name']} 2>&1", $result, $errCode);
        if ($errCode) {
            throw new \Exception(implode("\n", $result));
        }
    }
}
