<?php 
defined('BASEPATH') OR exit('No direct script access allowed');

$config['client_id'] = 'AUbh_JXsTbSU-qKuw2cR5TMVzTdMcFwFkEg9dTHEApigqGwaBFz1K19mmAMr9GWo-XA7P3sbfphMZZDo';
$config['secret'] = 'ENnasq6RpplLZhlodxG2Sea-YThmQxlgLJ7GDylhpJLDBMoO34RGzd28ClBGmuoEUjbkcWlu85LtM2mm';
$config['exchange_rate'] = 23000;

$config['settings'] = array(
    'mode' => 'sandbox', 
    'http.ConnectionTimeOut' => 30,
    'log.LogEnabled' => true,
    'log.FileName' => APPPATH . 'logs/paypal.log',
    'log.LogLevel' => 'DEBUG',
    'validation.level' => 'log',
    'cache.enabled' => true,
);

log_message('debug', 'PayPal config loaded: ' . json_encode($config));