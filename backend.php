<?php
$data = json_decode(file_get_contents('php://input'),true);

switch ($data["type"])
{
    case "OpenVPN":
        {
            if (isset($data["name"]) && isset($data["system"]))
                SendConfig($data["name"], $data["system"]);
            break;
        }
    case "Iptables":
        {
            if(isset($data["action"]))
            {
                $script_path = "router.sh";
                if($data["action"] == "get_templates")
                {
                    $templates = array(
                        "iptables -F\niptables -X\niptables -t nat -F\niptables -t nat -X\niptables -t mangle -F\niptables -t mangle -X",
                        "iptables -Z\niptables -t nat -Z\niptables -t mangle -Z",
                        "iptables -P INPUT ACCEPT\niptables -P OUTPUT ACCEPT\niptables -P FORWARD ACCEPT",
                        "iptables -A INPUT -i eth0 -p tcp --dport 22 -j DROP\niptables -A OUTPUT -o eth0 -p tcp --sport 22 -j DROP",
                        "iptables -A INPUT -i eth1 -s 192.168.0.0/24 -p tcp -j DROP\niptables -A OUTPUT -i eth1 -d 192.168.0.0/24 -p tcp -j DROP",
                        "iptables -A INPUT -p icmp --icmp-type any -j DROP\niptables -A OUTPUT -p icmp --icmp-type any -j DROP",
                        "iptables -A OUTPUT -m state --state NEW -o eth0 -j ACCEPT\niptables -A INPUT -m state --state ESTABLISHED,RELATED -j ACCEPT",
                        "iptables -t nat -A PREROUTING -i eth1 -p tcp --dport 22 -j DNAT --to-destination 192.168.15.50",
                        "iptables -t nat -A POSTROUTING -o eth1 -s 192.168.0.0/24 -j SNAT --to-source 192.168.15.50",
                        "iptables -t nat -A POSTROUTING -o eth1 -s 192.168.0.0/24 -j MASQUERADE"

                    );
                    echo json_encode($templates);
                }
                else if ($data["action"] == "get_file")
                {
                    $file = file($script_path);
                    echo json_encode($file);

                }
                else if ($data["action"] == "save_file")
                {

                    if($data["file"][0])
                    {

                    }
                    if (strpos($data["file"][0], '#!/bin/bash') !== false)
                        $file = "";
                    else
                        $file = "#!/bin/bash\n";
                    for ($i = 0; $i<count($data["file"]);$i++)
                    {
                        $file .= $data["file"][$i] . "\n";
                    }
                    file_put_contents($script_path,$file);
                    echo "success";
                }
            }
            break;
        }
    case "Memes":
        break;
    default:
        {
            header('HTTP/1.1 400 Bad Request');
            header('Content-Type: application/json; charset=UTF-8');
            die(json_encode(array('message' => 'ERROR', 'code' => 400)));
            break;
        }
}


function SendConfig($name, $system)
{
    $conf_path = "/etc/openvpn/client/configs/";
    $script_path = "/etc/openvpn/client/make_config.sh";
    $filename = GetFilename($name, $system);

    sleep(rand(1,3));
    if(shell_exec("bash {$script_path} {$name} {$system}") != null && file_exists( $conf_path.$filename)) // Script must return a value!!!
    {
        $file = file_get_contents($conf_path.$filename);
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        echo $file;
    }
    else
    {
        header('HTTP/1.1 404 Missing File');
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(array('message' => 'ERROR', 'code' => 404)));
    }
}

function GetFilename($name, $system)
{
    $filename = $name."_".$system;
    if ($system == "linux")
        $filename .= ".conf";
    else
        $filename .= ".ovpn";
    return $filename;
}