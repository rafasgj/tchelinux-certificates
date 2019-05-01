<?php

include('datafile.php');

function error($msg) {
    #global $event_data, $fingerprint;
    http_response_code(204);
    #echo "# ".$msg." # ".$event_data." # ".$fingerprint." #";
    exit();
}

function main() {
    global $argv;

    if (isset($_GET['event_info'])) {
        $fingerprint = $_GET['code'];
        $src_info = $_GET['event_info'];
    } else {
        if (count($argv) != 3) {
            echo "\nusage:\n\t${argv[0]} <fingerprint> <event_info>\n\n";
            exit();
        }
        $fingerprint = $argv[1];
        $src_info = $argv[2];
    }

    $json = file_get_contents(get_certificate_dbfile($src_info));
    if (! isset($json)) {
        //TODO: render error page. "Não foi possível encontrar os dados
        // do evento."
        echo "Cannot load data. Contact administrator.\n";
        exit();
    }
    $data = json_decode($json,true);

    $INSTITUICAO = $data['instituicao'];
    $CIDADE = $data['cidade'];
    $HORAS = $data['horas'];
    if (isset($data['horas_organizacao'])) {
        $HORAS_ORG = $data['horas_organizacao'];
    } else {
        $HORAS_ORG = 0;
    }
    $DATA = $data['data'];

    $fingerprint = trim($fingerprint);

    foreach ($data['participantes'] as $_ => $participante) {
        if ($fingerprint == trim($participante['fingerprint'])) {
            $EMAIL = trim($participante['email']);
            $FULANO = trim($participante['nome']);
            $PALESTRAS = array();
            $ORGANIZACAO = 0;
            if (in_array('organizador', $participante['roles'])) {
                $ORGANIZACAO = $HORAS_ORG;
            }
            if (in_array('palestrante', $participante['roles'])) {
                $PALESTRAS = $participante['palestras'];
            }
            $data = array('nome'=>$FULANO, 'data'=>$DATA, 'cidade'=>$CIDADE,
                          'horas'=>$HORAS, 'fingerprint' => $fingerprint,
                          'palestras'=>$PALESTRAS, 'organizacao'=>$ORGANIZACAO);
            $json = json_encode($data);
            http_response_code(200);
            echo $json;
            exit();
        }
    }

    error("Cannot find user.");
}

main()


?>
