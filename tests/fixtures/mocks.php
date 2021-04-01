<?php

declare(strict_types=1);

namespace Platine\Mail;

$mock_base64_encode = false;
$mock_chunk_split = false;
$mock_date = false;
$mock_filesize_to_false = false;
$mock_fread_to_false = false;
$mock_filter_var_to_false = false;


function filter_var($value, int $filter = FILTER_DEFAULT, $options = 0)
{
    global $mock_filter_var_to_false;
    if ($mock_filter_var_to_false) {
        return false;
    } else {
        return \filter_var($value, $filter, $options);
    }
}

function base64_encode(string $name)
{
    global $mock_base64_encode;
    if ($mock_base64_encode) {
        return $name;
    } else {
        return \base64_encode($name);
    }
}


function date(string $format)
{
    global $mock_date;
    if ($mock_date) {
        return '2021-01-01';
    } else {
        return \date($format);
    }
}

function filesize(string $filename)
{
    global $mock_filesize_to_false;
    if ($mock_filesize_to_false) {
        return false;
    } else {
        return \filesize($filename);
    }
}

function fread($stream, int $length)
{
    global $mock_fread_to_false;
    if ($mock_fread_to_false) {
        return false;
    } else {
        return \fread($stream, $length);
    }
}

function chunk_split(string $string, int $length = 76, string $separator = "\r\n"): string
{
    global $mock_chunk_split;
    if ($mock_chunk_split) {
        return $string;
    } else {
        return \chunk_split($string, $length, $separator);
    }
}

namespace Platine\Mail\Transport;

$mock_is_resource_array = false;
$mock_is_resource_array_content = false;
$mock_is_resource_to_true = false;
$mock_is_resource_to_false = false;
$mock_fgets_to_string = false;
$mock_fgets_return_content = false;
$mock_fclose_to_true = false;
$mock_fsockopen_to_true = false;
$mock_stream_socket_enable_crypto_to_false = false;
$mock_stream_socket_enable_crypto_to_true = false;
$mock_stream_set_timeout = false;
$mock_mail = false;
$mock_fputs = false;
$mock_popen_to_false = false;
$mock_popen_to_true = false;
$mock_is_dir_to_true = false;
$mock_is_dir_to_false = false;
$mock_is_writable_to_false = false;
$mock_is_writable_to_true = false;
$mock_file_put_contents_to_true = false;
$mock_file_put_contents_to_false = false;
$mock_pclose = false;
$mock_pclose_zero = false;
$mock_function_exists_to_false = false;
$mock_function_exists_to_true = false;


function fgets($handle)
{
    global $mock_fgets_to_string, $mock_fgets_return_content;

    if ($mock_fgets_to_string) {
        if (is_array($mock_fgets_return_content)) {
            $curr = current($mock_fgets_return_content);
            next($mock_fgets_return_content);

            return $curr;
        }
    }
    return \fgets($handle);
}

function fclose($handler)
{
    global $mock_fclose_to_true;
    if ($mock_fclose_to_true) {
        return true;
    }
    return \fclose($handler);
}

function is_resource($handler)
{
    global $mock_is_resource_to_false,
           $mock_is_resource_to_true,
           $mock_is_resource_array,
           $mock_is_resource_array_content;

    if ($mock_is_resource_to_true) {
        return true;
    } elseif ($mock_is_resource_to_false) {
        return false;
    } elseif ($mock_is_resource_array) {
        if (is_array($mock_is_resource_array_content)) {
            $curr = current($mock_is_resource_array_content);
            next($mock_is_resource_array_content);
            return $curr;
        }
    }
    return \is_resource($handler);
}

function fsockopen(
    string $hostname,
    int $port = -1,
    int &$error_code = null,
    string &$error_message = null,
    $timeout = null
) {
    global $mock_fsockopen_to_true;

    if ($mock_fsockopen_to_true) {
        return true;
    }

    return \fsockopen($hostname, $port, $error_code, $error_message, $timeout);
}

function stream_socket_enable_crypto($stream, bool $enable, int $crypto_type)
{
    global $mock_stream_socket_enable_crypto_to_false,
            $mock_stream_socket_enable_crypto_to_true;

    if ($mock_stream_socket_enable_crypto_to_true) {
        return true;
    } elseif ($mock_stream_socket_enable_crypto_to_false) {
        return false;
    }
    return \stream_socket_enable_crypto($stream, $enable, $crypto_type);
}

function stream_set_timeout($stream, int $seconds)
{
    global $mock_stream_set_timeout;
    if ($mock_stream_set_timeout) {
        return true;
    }
    return \stream_set_timeout($stream, $seconds);
}

function is_dir(string $filename)
{
    global $mock_is_dir_to_true, $mock_is_dir_to_false;
    if ($mock_is_dir_to_true) {
        return true;
    } elseif ($mock_is_dir_to_false) {
        return false;
    } else {
        return \is_dir($filename);
    }
}

function is_writable(string $filename)
{
    global $mock_is_writable_to_false, $mock_is_writable_to_true;
    if ($mock_is_writable_to_false) {
        return false;
    } elseif ($mock_is_writable_to_true) {
        return true;
    } else {
        return \is_writable($filename);
    }
}

function file_put_contents(string $filename, $data, int $flags = 0)
{
    global $mock_file_put_contents_to_true, $mock_file_put_contents_to_false;
    if ($mock_file_put_contents_to_true) {
        return true;
    } elseif ($mock_file_put_contents_to_false) {
        return false;
    } else {
        return \file_put_contents($filename, $data, $flags);
    }
}

function function_exists(string $name)
{
    global $mock_function_exists_to_false,
            $mock_function_exists_to_true;
    if ($mock_function_exists_to_false) {
        return false;
    } elseif ($mock_function_exists_to_true) {
        return true;
    } else {
        return \function_exists($name);
    }
}

function popen(string $command, string $mode)
{
    global $mock_popen_to_false, $mock_popen_to_true;
    if ($mock_popen_to_false) {
        return false;
    } elseif ($mock_popen_to_true) {
        return true;
    } else {
        return \popen($command, $mode);
    }
}

function pclose($handle)
{
    global $mock_pclose, $mock_pclose_zero;
    if ($mock_pclose) {
        return 1;
    } elseif ($mock_pclose_zero) {
        return 0;
    } else {
        return \pclose($handle);
    }
}

function mail(string $to, string $subject, string $message, $headers = [], string $additional_params = '')
{
    global $mock_mail;
    if ($mock_mail) {
        return true;
    } else {
        return \mail($to, $subject, $message, $headers, $additional_params);
    }
}

function fputs($handle, string $string)
{
    global $mock_fputs;
    if ($mock_fputs) {
        return true;
    } else {
        return \fputs($handle, $string);
    }
}
