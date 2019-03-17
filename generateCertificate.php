<?php
# Requires php-xml (utf8_encode/decode)

# 2014 - 6 de Dezembro de 2014
# 2015 - 28 de Novembro de 2015
# 2016 - 14 de Maio de 2016
# 2016b - 19 de Novembro de 2016
# 2017a - 20 de Maio de 2017

include("fpdf.php");

function wikify($pdf, $text)
{
    $count = 0;
    $bold = true;
    $a = split('@',$text);
    foreach ($a as $i => $e) {
        $bold = ! $bold;
        if ($bold) $pdf->SetFont('','B');
        else $pdf->SetFont('','');
        $pdf->Write(10,$e);
    }
}

function generate($values) {
    $FULANO = $values[0];
    $DATA = $values[1];
    $INSTITUICAO = $values[2];
    $CIDADE = $values[3];
    $HORAS = $values[4];
    $HORAS_ORGANIZACAO = $values[5];
    $FINGERPRINT = $values[6];
    $ROLES = $values[7];
    $PALESTRAS = $values[8];

    $MES = array("","Janeiro","Fevereiro","Março","Abril",
                 "Maio","Junho","Julho","Agosto","Setembro",
                 "Outubro","Novembro","Dezembro");

    $dia = $DATA[2]." de ".$MES[(int)$DATA[1]]." de ".$DATA[0];

    $MARGIN = 25;

    $pdf = start_document($MARGIN);

    $valid_roles = array();
    if (in_array("organizador", $ROLES)) {
        array_push($valid_roles, "organizador");
    } else if (in_array("participante", $ROLES)) {
        array_push($valid_roles, "participante");
    }
    if (in_array("palestrante", $ROLES)) {
        array_push($valid_roles, "palestrante");
    }

    foreach ($valid_roles as $i => $ROLE) {
        $ROLE = trim($ROLE);
        $TYPE = "Participante";
        if (strtolower($ROLE) == "organizador") {
            $TYPE = "Organizador";
        } elseif  (strtolower($ROLE) == "palestrante") {
            continue;
        }

        new_page($pdf);

        generate_header($pdf, $MARGIN, $TYPE, $CIDADE, $DATA[0]);

        render_recipient($pdf, $MARGIN, $FULANO);

        if ($TYPE == "Organizador") {
            generate_organization($pdf, $INSTITUICAO, $dia, $HORAS_ORGANIZACAO);
        } elseif ($TYPE == "Participante") {
            generate_participant($pdf, $INSTITUICAO, $dia, $HORAS);
        }
        generate_footer($pdf, $FINGERPRINT);
    }

    $TYPE = "Palestrante";
    foreach ($PALESTRAS as $i => $PALESTRA) {
        new_page($pdf);
        generate_header($pdf, $MARGIN, $TYPE, $CIDADE, $DATA[0]);
        render_recipient($pdf, $MARGIN, $FULANO);
        generate_speech($pdf, $INSTITUICAO, $dia, $PALESTRA);
        generate_footer($pdf, $FINGERPRINT);
    }

    $pdf->Output();
}

function new_page($pdf) {
    $pdf->AddPage();
    $pdf->SetX(0);
    $pdf->Sety(0);
    $pdf->Image('images/background.jpg',90,-10,220);
}

function start_document($MARGIN) {
    $pdf = new FPDF('L','mm','A4');

    $pdf->SetAuthor("Tchelinux");
    $pdf->SetCreator("Tchelinux.org");
    $pdf->SetTitle("Certificado Tchelinux");
    $pdf->SetSubject("Certificado Tchelinux");

    $pdf->SetLeftMargin($MARGIN);
    $pdf->SetRightMargin($MARGIN);

    return $pdf;
}

function render_recipient($pdf, $MARGIN, $FULANO) {
    $pdf->SetFont('Times','',28);
    $pdf->Cell(297-2*$MARGIN,90,utf8_decode($FULANO),0,1,'C');
}

function render_commom_text($pdf) {
    $pdf->SetFont('Times','',18);
    $pdf->SetY(85);
    $pdf->Write(10,utf8_decode("O Grupo de Usuários de Software Livre Tchelinux certifica que"));
    $pdf->SetY(115);
}

function generate_speech($pdf, $INSTITUICAO, $DATE, $PALESTRA) {
    render_commom_text($pdf);
    $pdf->SetFont('Times','',18);
    $pdf->Write(10,utf8_decode("apresentou a palestra "));
    $pdf->SetFont('Times','B',18);
    $pdf->Write(10,utf8_decode($PALESTRA));
    $pdf->SetFont('Times','',18);
    $pdf->Write(10, utf8_decode(" no evento realizado em "));
    $pdf->Write(10,utf8_decode("$DATE, nas dependências da $INSTITUICAO."));
}

function generate_participant($pdf, $INSTITUICAO, $DATE, $HORAS) {
    render_commom_text($pdf);
    $pdf->Write(10,utf8_decode("esteve presente ao evento realizado em "));
    $pdf->Write(10,utf8_decode("$DATE, nas dependências da $INSTITUICAO, "));
    $pdf->Write(10,utf8_decode("com duração de $HORAS horas."));
}

function generate_organization($pdf, $INSTITUICAO, $DATE, $HORAS) {
    render_commom_text($pdf);
    $pdf->Write(10,utf8_decode("colaborou na organização do evento realizado em "));
    $pdf->Write(10,utf8_decode("$DATE, nas dependências da $INSTITUICAO, "));
    $pdf->Write(10,utf8_decode("por $HORAS horas."));
}

function generate_header($pdf, $MARGIN, $TYPE, $CIDADE, $DATE) {
    $pdf->SetFont('Arial','B',36);
    $pdf->Cell(297-2*$MARGIN,25,'CERTIFICADO DE '.utf8_decode(strtoupper($TYPE)),0,1,'C');

    $pdf->SetFont('Arial','B',24);
    $msg = 'Seminário de Software Livre';
    $pdf->Cell(297-2*$MARGIN,35,utf8_decode($msg),0,1,'C');
    $pdf->SetFont('Arial','B',32);
    $pdf->Cell(297-2*$MARGIN,0,'Tchelinux '.utf8_decode($CIDADE).' '.$DATE,0,1,'C');
}

function generate_footer($pdf, $FINGERPRINT) {
    $pdf->SetFont('Times','',12);
    $pdf->SetY(-30.5);
    $pdf->SetX(-80);
    $pdf->Cell(21,0,utf8_decode("Código de Verificação:"),0,0,'R');
    $pdf->SetY(-25);
    $pdf->SetX(-114);
    $pdf->SetFont('','B');
    $pdf->Cell(10,0,$FINGERPRINT,0,1,'L');
    $pdf->SetFont('','');
    $pdf->SetY(-20.5);
    $pdf->SetX(-65.5);
    $pdf->Cell(15,0,'https://certificados.tchelinux.org',0,0,'R');
}


function main() {
    global $argv;

    if (isset($_GET['event_info'])) {
        $event_info = explode(":",$_GET['event_info']);
        $EMAIL = $_GET['email'];
    } else {
        if (count($argv) != 3) {
            echo "\nusage:\n\t${argv[0]} <email> <event_info>\n\n";
            exit();
        }
        $EMAIL = $argv[1];
        $event_info = explode(":",$argv[2]);
    }

    $DATA = $event_info[0];
    $CODENAME = $event_info[1];

    $filename = 'data/'.$DATA.'-'.$CODENAME.'.json';

    $json = file_get_contents($filename);
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
        $HORAS_ORG = null;
    }
    $DATA = $data['data'];

    $EMAIL = strtolower(trim($EMAIL));

    foreach ($data['participantes'] as $_ => $participante) {
        if ($EMAIL == strtolower(trim($participante['email']))) {
            $FULANO = trim($participante['nome']);
            if (isset($participante['roles']))
                $ROLES = $participante['roles'];
            else if (isset($participante['role']))
                $ROLES = array($participante['role']);
            else
                $ROLES = array("participante");
            $FINGERPRINT = trim($participante['fingerprint']);
            if (isset($participante['palestras'])) {
                $PALESTRAS = $participante['palestras'];
            } else {
                $PALESTRAS = array();
            }
            break;
        }
    }

    if (!isset($FULANO)) {
        //TODO: render error page. "Não foi possível encontrar os dados
        // do participante."
        echo "Cannot find user for email: $EMAIL.\n";
        exit();
    }

    $values = array($FULANO, explode("-",$DATA), $INSTITUICAO, $CIDADE,
                    $HORAS, $HORAS_ORG, $FINGERPRINT, $ROLES, $PALESTRAS);
    generate($values);
}

main()

?>
