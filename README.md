# luthor

## Pre Installation

Luthor depends on several systems to work. 

1. LXC as system container
2. Libvirt as networking support
3. Btrfs as filesystem

### Sudoers

www-data ALL=NOPASSWD: /usr/bin/lxc-*
www-data ALL=NOPASSWD: /var/www/luthor/bin/luthor-*
www-data ALL=NOPASSWD: /usr/bin/virsh

## Features

Luthor supports linux container system management with several features available:

- Virtual server with Linux container capabilities
- Virtual networking with the use of libvirt networking supports
- LXC Templates management
- Create new with snapshot (backing store btrfs)
