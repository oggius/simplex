<?php
namespace application\services;

use application\helpers\TwoCheckoutHelper;
use application\models\customer\CustomerManager;
use application\models\order\Order;
use application\models\subscription\SubscriptionManager;
use system\basic\Config;

/**
 * Class TwoCheckoutEmulator
 * @package application\services
 */
class TwoCheckoutEmulator {

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var array
     */
    protected $_notificationParams = array();

    /**
     * @param Order $order
     * @throws \system\basic\exceptions\WrongConfigException
     */
    public function __construct(Order $order)
    {
        if (!$order->isVoid()) {
            $secureParams = Config::getSectionParam('secure', Config::getSectionParam('application', 'mode'));
            $checkoutParams = $secureParams['2checkout'];

            $customerManager = new CustomerManager();
            $customer = $customerManager->getCustomerById($order->customer_id);

            $subscriptionManager = new SubscriptionManager();
            $subscriptionInfo = $subscriptionManager->getSubscriptionInfoById($order->subscription_id);
            $this->_order = $order;
            $this->_notificationParams = array(
                'middle_initial' => '',
                'sid' => $checkoutParams['sid'],
                'key' => '',
                'state' => 'asd',
                'email' => $customer->email,
                'order_number' => '',
                'product_description' => $subscriptionInfo['title'],
                'lang' => 'en',
                'currency_code' => 'USD',
                'invoice_id' => '',
                'total' => $order->amount,
                'credit_card_processed' => 'Y',
                'zip' => '12321',
                'ip' => $customer->ip,
                'cart_weight' => '0',
                'fixed' => 'Y',
                'last_name' => $customer->lastname,
                'street_address' => '',
                'city' => '',
                'product_id' => TwoCheckoutHelper::convertProductId($subscriptionInfo['id']),
                'merchant_order_id' => $order->cart_order_id,
                'country' => 'UKR',
                'ip_country' => 'Ukraine',
                'demo' => 'N',
                'quantity' => 1,
                'pay_method' => 'CC',
                'cart_tangible' => 'N',
                'phone' => '',
                'merchant_product_id' => $subscriptionInfo['id'],
                'street_address2' => '',
                'card_holder_name' => $customer->username,
                'first_name' => $customer->firstname,
            );
        }
    }

    /**
     * @return array
     */
    public function sendOrderSuccessNotification()
    {
        $this->_notificationParams['sale_id'] = mt_rand(1, 99999999);
        $this->_notificationParams['invoice_id'] = mt_rand(1, 99999999);
        $this->_notificationParams['order_number'] = $this->_notificationParams['sale_id'];
        return $this->_notificationParams;
    }

    /**
     * @return array
     */
    public function sendOrderConfirmedNotification()
    {
        $this->_notificationParams['sale_id'] = $this->_order->payment_confirmation;
        $this->_notificationParams['invoice_id'] = mt_rand(1, 99999999);
        $this->_notificationParams['fraud_status'] = 'pass';
        return $this->_notificationParams;
    }

    /**
     * @return array
     */
    public function sendRecurringSuccessNotification()
    {
        $this->_notificationParams['message_type'] = 'RECURRING_INSTALLMENT_SUCCESS';
        $this->_notificationParams['message_description'] = 'Recurring installment successfully billed';
        $this->_notificationParams['sale_id'] = $this->_order->payment_confirmation;
        $this->_notificationParams['invoice_id'] = mt_rand(1, 99999999);
        $this->_notificationParams['item_id_1'] = $this->_order->subscription_id;
        $this->_notificationParams['item_rec_list_amount_1'] = $this->_order->amount;
        $this->_notificationParams['vendor_order_id'] = $this->_order->cart_order_id;
        unset($this->_notificationParams['merchant_order_id']);

        return $this->_notificationParams;
    }

    /**
     * @return array
     */
    public function sendRecurringFailedNotification()
    {
        $this->_notificationParams['message_type'] = 'RECURRING_INSTALLMENT_FAILED';
        $this->_notificationParams['message_description'] = 'Recurring installment failed to bill';
        $this->_notificationParams['sale_id'] = $this->_order->payment_confirmation;
        $this->_notificationParams['invoice_id'] = mt_rand(1, 99999999);
        $this->_notificationParams['item_id_1'] = $this->_order->subscription_id;
        $this->_notificationParams['item_rec_list_amount_1'] = $this->_order->amount;
        $this->_notificationParams['vendor_order_id'] = $this->_order->cart_order_id;
        unset($this->_notificationParams['merchant_order_id']);

        return $this->_notificationParams;
    }

    /**
     *
     */
    public function sendRecurringStoppedNotification()
    {}
}