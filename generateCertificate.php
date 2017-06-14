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

function generate($FULANO, $EMAIL, $DATA, $LOCAL, $HORAS) {

    $MES = array("","Janeiro","Fevereiro","Março","Abril",
                 "Maio","Junho","Julho","Agosto","Setembro",
                 "Outubro","Novembro","Dezembro");

    $dia = $DATA['dia']." de ".$MES[$DATA['mes']]." de ".$DATA['ano'];

    $finger = hash("md5",$FULANO.$EMAIL.$LOCAL.$dia);

    $MARGIN = 25;

    $pdf = new FPDF('L','mm','A4');
    $pdf->SetLeftMargin($MARGIN);
    $pdf->SetRightMargin($MARGIN);
    $pdf->AddPage();

    $pdf->Image('background.png',90,-10,220);

    $pdf->SetFont('Arial','B',48);
    $pdf->Cell(297-2*$MARGIN,25,'CERTIFICADO',0,1,'C');

    $pdf->SetFont('Arial','B',24);
    $msg = 'Seminário de Software Livre';
    $pdf->Cell(297-2*$MARGIN,35,utf8_decode($msg),0,1,'C');
    $pdf->SetFont('Arial','B',32);
    $pdf->Cell(297-2*$MARGIN,0,'TcheLinux Porto Alegre '.$DATA['ano'],0,1,'C');

    $pdf->SetFont('Times','',28);
    $pdf->Cell(297-2*$MARGIN,90,utf8_decode($FULANO),0,1,'C');

    $pdf->SetFont('Times','',18);

    $pdf->SetY(95);
    $pdf->Write(10,utf8_decode("O Grupo de Usuários de Software Livre TcheLinux certifica que"));
    $pdf->SetY(125);
    $pdf->Write(10,"esteve presente ao evento realizado em ");
    $pdf->Write(10,utf8_decode($dia));
    $pdf->Write(10,utf8_decode(", com duração de $HORAS horas,"));
    $pdf->Write(10,utf8_decode(" nas dependências da "));
    $pdf->Write(10,utf8_decode($LOCAL));
    $pdf->Write(10,".");

    $pdf->SetFont('Times','',12);
    $pdf->SetY(-30.5);
    $pdf->SetX(-78);
    $pdf->Cell(21,0,utf8_decode("Código de Confirmação:"),0,0,'R');
    $pdf->SetY(-25);
    $pdf->SetX(-114);
    $pdf->SetFont('','B');
    $pdf->Cell(10,0,$finger,0,1,'L');
    $pdf->SetFont('','');
    $pdf->SetY(-20.5);
    $pdf->SetX(-65.5);
    $pdf->Cell(15,0,'https://certificados.tchelinux.org',0,0,'R');

    $pdf->Output();
}

function main() {
    if (isset($_GET['FULANO'])) {
        $FULANO = $_GET['FULANO'];
        $EMAIL = $_GET['FULANO'];
        $dia = explode('-',trim($_GET['DATA']));
        $LOCAL = $_GET['LOCAL'];
        $HORAS = $_GET['HORAS'];
    } else {
        $FULANO = "Emerson Roberto Gonçalves Nunes";
        $EMAIL = "emerson@example.com";
        $dia = array(2016,11,19);
        $LOCAL = "Faculdade SENAC de Porto Alegre";
        $HORAS = 5;
    }
    $DATA = array('dia'=>$dia[2],'mes'=>$dia[1],'ano'=>$dia[0]);
    generate($FULANO, $EMAIL, $DATA, $LOCAL, $HORAS);
}

main()

?>
