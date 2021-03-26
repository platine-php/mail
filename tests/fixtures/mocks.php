<?php

declare(strict_types=1);

namespace Platine\Mail;

$mock_extension_loaded = false;
$mock_iconv_mime_encode_false = false;
$mock_iconv_mime_encode_string = false;
$mock_iconv_strlen_false = false;
$mock_iconv_strlen_int = false;

function extension_loaded(string $name)
{
    global $mock_extension_loaded;
    if ($mock_extension_loaded) {
        return true;
    } else {
        return \extension_loaded($name);
    }
}

function iconv_mime_encode(string $field_name, string $field_value, array $options = [])
{
    global $mock_iconv_mime_encode_false, $mock_iconv_mime_encode_string;
    if ($mock_iconv_mime_encode_false) {
        return false;
    } elseif ($mock_iconv_mime_encode_string) {
        return 'foobar';
    } else {
        return \iconv_mime_encode($field_name, $field_value, $options);
    }
}

function iconv_strlen(string $str, string $encoding = null)
{
    global $mock_iconv_strlen_false, $mock_iconv_strlen_int;
    if ($mock_iconv_strlen_false) {
        return false;
    } elseif ($mock_iconv_strlen_int) {
        return 123;
    } else {
        return \iconv_strlen($str, $encoding);
    }
}
