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
        break;
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

    sleep(rand(1,5));
    if(shell_exec($script_path) && file_exists( $conf_path.$filename))
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