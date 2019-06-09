<?php
    require 'datafile.php';
    function generate_event_option_list($value='')
    {
        $cities = json_decode(file_get_contents('citylist.json'), true);
        foreach (get_certificate_list() as $fname => $type) {
            if ($type == "json") {
                $data = explode("-", $fname);
                $date = implode("-", array_slice($data,0,3));
                $ano = $data[0];
                $cname = $data[3];
                $code = $date.":".$cname;
                $cidade = $cities[$cname];
                echo '<option value="'.$code.'">';
                echo $cidade.", $ano</option>";
            }
        }
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tchelinux - Emissão de Certificados</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-1.11.0.min.js"></script>
    <!-- OpenGraph -->
    <meta property="og:image" content="https://tchelinux.org/assets/tchelinux-facebook.png">
    <!-- bootstrap css -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap-theme.min.css">
    <!--Bootstrap -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
    <!-- Tchelinux Style -->
    <link rel="stylesheet" type="text/css" href="css/style.css">
</head>
<body>
<header class="header">
    <div class="wrapper">
        <h1 class="logo-name">
            <a class="logo-link" href="#" title="Tchelinux Santa Cruz" itemprop="name">Tchelinux<br/>
            Certificados Digitais</a>
        </h1>
    </div>
</header>

    <section id="requisitar">
        <div class="container">
            <h2>Requisição de Certificados Digitais</h2>
            <form method="GET" action="generateCertificate.php" target="_blank">
                <div class="form-group">
                    <select id="event_info" name="event_info">
                        <?php generate_event_option_list(); ?>
                    </select>
                    <input type="text" size="32" id="email" name="email" placeholder="E-Mail de inscrição"></input>
                    <button style="vertical-align: bottom" type="submit" class="btn btn-default">Requisitar Certificado</button>
            </form>
        </div>
    </section>
    <section id="validar">
        <div class="container">
            <h2>Verificação de Certificados Digitais</h2>
            <form method="GET" action="validateCertificate.php" id="validateform">
                <div class="form-group">
                    <select id="event_info" name="event_info">
                        <?php generate_event_option_list(); ?>
                    </select>
                    <input type="text" size="32" id="code" name="code" placeholder="Código de Verificação"></input>
                    <button style="vertical-align: bottom" type="submit" class="btn btn-default">Validar Certificado</button>
                </div>
            </form>
            <script type='text/javascript'>
                    /* attach a submit handler to the form */
                    $("#validateform").submit(function(event) {
                        /* stop form from submitting normally */
                        event.preventDefault()
                        /* get the action attribute from the <form action=""> element */
                        var $form = $(this), url = $form.attr('action')
                        /* Send the data using post with element id name and name2*/
                        $.get(url, {
                                event_info: $('#validateform #event_info').val(),
                                code: $('#code').val()
                            }
                        ).done(function(data, textStatus, response) {
                            if (response.status == 200) {
                                display_validation_success(jQuery.parseJSON(data))
                            } else {
                                display_validation_error()
                            }
                        }).fail(function(data, textStatus, response) {
                            alert(response + ": " + textStatus)
                            alert("Something went pretty wrong when validating code.")
                        });
                    });
            </script>
        </div>
    </section>

    <section id="resultado">
        <div class="container">
            <p style="text-align:center">O certificado é:
            <span class="label label-success" id="validate_label">VÁLIDO</span></p>
            <div id="result-error">
                <p>Para o evento</b><p style="text-align:center"><b id="msg"></b></p>
                <p>Confira o evento e o código de verificação.</p>
            </div>
            <div id="result-data">
                <p>Nome: <b id="participante"></b></p>
                <p>Data: <b id="data"></b></p>
                <p>Evento: <b id="cidade"></b></p>
                <p id="participante">Horas de Participação: <b id="horas"></b></p>
                <p id="organizacao">Horas de Organização: <b id="horas_organizacao"></b></p>
                <p id="palestras">Palestras:<ul id="list_palestras"></ul>
                <p style="text-align:center">Codigo de Verificação:<br/><b id="fingerprint"></b></p>
            </div>
        </div>
    </section>
    <script>
    function display_validation_success(obj) {
        $('#resultado').css('display','block');
        $('#result-data').css('display','block');
        $('#result-error').css('display','none');
        $('#resultado #participante').text(obj.nome)
        $('#resultado #data').text(obj.data)
        $('#resultado #cidade').text(obj.cidade)
        $('#resultado #horas').text(obj.horas)
        $('#resultado #fingerprint').text(obj.fingerprint)
        $('#resultado #horas_organizacao').text(obj.organizacao)
        if (obj.horas > 0) {
            $('#resultado #participante').css('display', 'block')
        } else {
            $('#resultado #participante').css('display', 'none')
        }
        if (obj.organizacao > 0) {
            $('#resultado #organizacao').css('display', 'block')
        } else {
            $('#resultado #organizacao').css('display', 'none')
        }
        if (obj.palestras.length > 0) {
            $('#resultado #palestras').css('display', 'block')
            $('#list_palestras').empty()
            for (p in obj.palestras) {
                titulo = obj.palestras[p]
                $('#list_palestras').append($('<li></li>').text(titulo))
            }
        } else {
            $('#resultado #palestras').css('display', 'none')
        }

        $('#validate_label').removeClass("label-danger")
        $('#validate_label').addClass("label-success")
        $('#validate_label').css('display','inline-block')
        $('#validate_label').css('font-size','14px')
        $('#validate_label').text("VÁLIDO")
    }

    function display_validation_error() {
        $('#resultado').css('display','block')
        $('#result-data').css('display','none')
        $('#result-error').css('display','block')
        var msg = $('#validateform #event_info :selected').text()
        $('#result-error #msg').text(msg);
        $('#validate_label').removeClass("label-success")
        $('#validate_label').addClass("label-danger")
        $('#validate_label').css('display','inline-block')
        $('#validate_label').css('font-size','x-large')
        $('#validate_label').text("INVÁLIDO")
    }
    </script>
</body>
</html>
