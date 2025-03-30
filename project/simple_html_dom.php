<?php 
class simple_html_dom {
    public function load($str, $lowercase = true, $stripRN = true) {
        return str_get_html($str);
    }
}

function str_get_html($str) {
    $dom = new simple_html_dom_node();
    $dom->innertext = $str;
    return $dom;
}

class simple_html_dom_node {
    public $innertext;
    public function find($selector) {
        return [];
    }
}

function file_get_html_curl($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $contents = curl_exec($ch);
    $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($contents === false) {
        return ["error" => "cURL Error: $curl_error"];
    }

    if ($http_status != 200) {
        return ["error" => "HTTP Status: $http_status"];
    }

    $dom = new simple_html_dom();
    return $dom->load($contents, true, true);
}
?>
