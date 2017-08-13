<?php

function error($msg) {
    #global $event_data, $fingerprint;
    http_response_code(204);
    #echo "# ".$msg." # ".$event_data." # ".$fingerprint." #";
    exit();
}

if (isset($_GET['event_info'])) {
    $fingerprint = $_GET['code'];
    $event_data = $_GET['event_info'];
} else {
    if (count($argv) != 3) {
        echo "\nusage:\n\t${argv[0]} <fingerprint> <event_info>\n\n";
        exit();
    }
    $fingerprint = $argv[1];
    $event_data = $argv[2];
}

$event_info = explode(":",$event_data);
$DATA = $event_info[0];
$CODENAME = $event_info[1];

$filename = 'data/'.$DATA.'-'.$CODENAME.'.json';

$json = file_get_contents($filename);
if (! isset($json)) {
    //TODO: render error page. "Não foi possível encontrar os dados
    // do evento."
    error("Cannot read JSON file.");
}
$data = json_decode($json,true);

$INSTITUICAO = $data['instituicao'];
$CIDADE = $data['cidade'];
$HORAS = $data['horas'];
$DATA = $data['data'];

$fingerprint = trim($fingerprint);

foreach ($data['participantes'] as $_ => $participante) {
    if ($fingerprint == trim($participante['fingerprint'])) {
        $EMAIL = trim($participante['email']);
        $FULANO = trim($participante['nome']);
        $data = array('nome'=>$FULANO, 'data'=>$DATA, 'cidade'=>$CIDADE,
                      'horas'=>$HORAS, 'fingerprint' => $fingerprint);
        $json = json_encode($data);
        http_response_code(200);
        echo $json;
        exit();
    }
}

error("Cannot find user.");

?>
