#!/bin/bash
iptables -F
iptables -X
iptables -t nat -A PREROUTING -i eth1 -p tcp --dport 22 -j DNAT --to-destination 192.168.15.50
iptables -t nat -F
iptables -t nat -A POSTROUTING -o eth1 -s 192.168.0.0/24 -j MASQUERADE
iptables -t nat -X
iptables -t mangle -F
iptables -t mangle -X
iptables -Z
iptables -t nat -Z
iptables -t mangle -Z
iptables -P INPUT ACCEPT
iptables -P OUTPUT ACCEPT
iptables -P FORWARD ACCEPT
iptables -t nat -A POSTROUTING -o eth1 -s 192.168.0.0/24 -j MASQUERADE

