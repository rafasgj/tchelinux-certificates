<?php

function get_certificate_dbfile($event_info) {
    $info = explode(":", $event_info);
    $DATA = $info[0];
    $CODENAME = $info[1];
    $base = "/var/www/tchelinux.org/dados_certificados";
    $filename = $base.'/'.$DATA.'-'.$CODENAME.'.json';
    return $filename;
}

?>
