<network>
    <name><?php echo @$entry['name'] ?></name>
    <forward mode="nat">
        <nat>
            <port start="1024" end="65535"/>
        </nat>
    </forward>
    <bridge name="<?php echo @$entry['bridge'] ?>" stp="on" delay="0"/>
    <ip address="<?php echo @$entry['ip_address'] ?>" netmask="<?php echo @$entry['netmask'] ?>">
        <dhcp>
            <range start="<?php echo @$entry['dhcp_start'] ?>" end="<?php echo @$entry['dhcp_end'] ?>"/>
        </dhcp>
    </ip>
</network>