<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\PaymentExecution;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class Paypal_lib
{
    private $_api_context;
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        $this->CI->config->load('paypal', TRUE);

        $settings = $this->CI->config->item('settings', 'paypal');
        if (is_null($settings)) {
            log_message('error', 'PayPal settings not found or null in config/paypal.php');
            show_error('PayPal configuration error: Settings not found.');
        }

        // Khởi tạo ApiContext
        $this->_api_context = new ApiContext(
            new OAuthTokenCredential(
                $this->CI->config->item('client_id', 'paypal'),
                $this->CI->config->item('secret', 'paypal')
            )
        );
        $this->_api_context->setConfig($settings);
    }

    public function create_payment($total_usd, $order_code, $description)
    {
        if (!is_numeric($total_usd) || $total_usd <= 0) {
            log_message('error', 'Invalid total_usd: ' . $total_usd);
            return false;
        }

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setTotal(number_format($total_usd, 2, '.', '')); // Đảm bảo định dạng số
        $amount->setCurrency('USD');

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setDescription($description)
            ->setInvoiceNumber($order_code);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(site_url('giohang/paypal_success'))
            ->setCancelUrl(site_url('giohang/paypal_cancel'));

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setTransactions(array($transaction))
            ->setRedirectUrls($redirectUrls);

        try {
            $payment->create($this->_api_context);
            log_message('debug', 'PayPal payment created: ' . json_encode($payment->toArray()));
            return $payment;
        } catch (Exception $ex) {
            log_message('error', 'PayPal Create Payment Error: ' . $ex->getMessage());
            return false;
        }
    }

    public function execute_payment($payment_id, $payer_id)
    {
        $payment = Payment::get($payment_id, $this->_api_context);
        $execution = new PaymentExecution();
        $execution->setPayerId($payer_id);

        try {
            $result = $payment->execute($execution, $this->_api_context);
            log_message('debug', 'PayPal payment executed: ' . json_encode($result->toArray()));
            return $result;
        } catch (Exception $ex) {
            log_message('error', 'PayPal Execute Payment Error: ' . $ex->getMessage());
            return false;
        }
    }
}