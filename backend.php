<?php
$data = json_decode(file_get_contents('php://input'),true);
/*$result = 0;
if ($data["type"] == "toIn")
    $result = $data["value"] * 0.393701;
else
    $result = $data["value"] * 2.54;

echo $result;*/

switch ($data["type"])
{
    case "OpenVPN":
        if (isset($data["name"]) && isset($data["system"]))
            SendConfig($data["name"], $data["system"]);
        break;
    case "Iptables":
        break;
    case "Memes":
        break;
    default:
        echo "Error";
        break;
}



/*header('Content-Disposition: attachment; filename="filename.txt"');
$file = file_get_contents("files/Hola.txt");
echo $file;*/

function SendConfig($name, $system)
{
    $conf_path = "files/";
    $filename = GetFilename($name, $system);

    if(file_exists($conf_path.$filename))
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