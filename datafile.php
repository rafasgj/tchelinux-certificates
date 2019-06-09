<?php

function get_certificate_dbfile($event_info) {
    $base = "/var/www/tchelinux.org/dados_certificados";
    $info = explode(":", $event_info);
    $DATA = $info[0];
    $CODENAME = $info[1];
    $filename = $base.'/'.$DATA.'-'.$CODENAME.'.json';
    return $filename;
}

function get_certificate_list() {
    $base = "/var/www/tchelinux.org/dados_certificados";
    $res = array();
    foreach (scandir($base, SCANDIR_SORT_ASCENDING) as $_ => $filename) {
        $k = pathinfo($base."/".$filename, PATHINFO_FILENAME);
        $v = pathinfo($base."/".$filename, PATHINFO_EXTENSION);
        $res[$k] = $v;
    }
    ksort($res);
    return array_reverse($res);
}

?>
