<?php

/**
 * This file is used by tests to overload and mock various socket/stream calls.
 */

namespace WebSocket;

function stream_socket_server($local_socket, &$errno, &$errstr)
{
    $args = [$local_socket, $errno, $errstr];
    return MockSocket::handle('stream_socket_server', $args);
}
function stream_socket_accept()
{
    $args = func_get_args();
    return MockSocket::handle('stream_socket_accept', $args);
}
function stream_set_timeout()
{
    $args = func_get_args();
    return MockSocket::handle('stream_set_timeout', $args);
}
function stream_get_line()
{
    $args = func_get_args();
    return MockSocket::handle('stream_get_line', $args);
}
function stream_get_meta_data()
{
    $args = func_get_args();
    return MockSocket::handle('stream_get_meta_data', $args);
}
function feof()
{
    $args = func_get_args();
    return MockSocket::handle('feof', $args);
}
function ftell()
{
    $args = func_get_args();
    return MockSocket::handle('ftell', $args);
}
function fclose()
{
    $args = func_get_args();
    return MockSocket::handle('fclose', $args);
}
function fwrite()
{
    $args = func_get_args();
    return MockSocket::handle('fwrite', $args);
}
function fread()
{
    $args = func_get_args();
    return MockSocket::handle('fread', $args);
}
function fgets()
{
    $args = func_get_args();
    return MockSocket::handle('fgets', $args);
}
function stream_context_create()
{
    $args = func_get_args();
    return MockSocket::handle('stream_context_create', $args);
}
function stream_socket_client($remote_socket, &$errno, &$errstr, $timeout, $flags, $context)
{
    $args = [$remote_socket, $errno, $errstr, $timeout, $flags, $context];
    return MockSocket::handle('stream_socket_client', $args);
}
function get_resource_type()
{
    $args = func_get_args();
    return MockSocket::handle('get_resource_type', $args);
}
function stream_socket_get_name()
{
    $args = func_get_args();
    return MockSocket::handle('stream_socket_get_name', $args);
}
