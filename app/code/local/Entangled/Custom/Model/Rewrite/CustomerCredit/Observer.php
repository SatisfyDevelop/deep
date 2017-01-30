<?php

class Entangled_Custom_Model_Rewrite_CustomerCredit_Observer extends MageWorx_CustomerCredit_Model_Observer {

    /**
     * Add customercredit link to head
     * @param Varien_Event_Observer $observer
     */
    public function toHtmlBlockBefore(Varien_Event_Observer $observer) {
        $block = $observer->getEvent()->getBlock();
        $blockName = $block->getNameInLayout();
        if ($blockName == 'customer_account_navigation') {
            if (Mage::helper('mageworx_customercredit')->isShowCustomerCredit()) $block->addLink('customercredit', 'customercredit', Mage::helper('mageworx_customercredit')->__('My Rewards Points'),array("_secure"=>true));
        }
    }

    /**
     * Add rules to observer
     * @param Mage_Sales_Model_Order $order
     * @return MageWorx_CustomerCredit_Model_Observer
     */
    public function placeFirstOrderCustomer($order) {
        if(!$order->getQuote()) return;
        $customer = $order->getQuote()->getCustomer();
        Mage::getModel('mageworx_customercredit/credit', $customer)->processFirstOrder($order);

        return $this;
    }

    public function checkCompleteStatusOrder(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();


//        if(!Mage::registry("initial_reward") && Mage::helper('mageworx_customercredit')->isFirstTime()){
//            $this->placeFirstOrderCustomer($order);
//            Mage::register("initial_reward",true);
//        }

        return parent::checkCompleteStatusOrder($observer); // TODO: Change the autogenerated stub
    }

    public function sendNewPointsEmail($order){
        $recipientEmail = $order->getCustomerEmail();
        $recipientName = $order->getCustomerName();
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        $creditBalance = Mage::getModel('mageworx_customercredit/credit', $customer)->getValue();
        $sender = array(
            'name' => Mage::getStoreConfig('trans_email/ident_general/name'),
            'email' => Mage::getStoreConfig('trans_email/ident_general/email')
        );
        $vars = array(
            'customer_name' => $recipientName,
            'points_balance' => $creditBalance,
        );
        $storeId = Mage::app()->getStore()->getId();
        Mage::getModel('core/email_template')
            ->sendTransactional('entangled_rewardpoints_new_points', $sender, $recipientEmail, $recipientName, $vars, $storeId);
    }

    /**
     * Add rules to observer
     * @param Mage_Sales_Model_Order $order
     * @return MageWorx_CustomerCredit_Model_Observer
     */
    public function placeOrderCustomer($order) {
        if(!$order->getQuote()) return;
        $customer = $order->getQuote()->getCustomer();
        $handler = Mage::getSingleton('mageworx_customercredit/rules_handler');
        $handler->setCustomer($customer);
        $handler->setOrder($order);

        $customerGroupId = $customer->getGroupId();

        $store = Mage::app()->getStore($customer->getStoreId());
        $websiteId = $store->getWebsiteId();

        $ruleModel = Mage::getResourceModel('mageworx_customercredit/rules_collection');
        $ruleModel->setValidationFilter($websiteId, $customerGroupId)->setRuleTypeFilter(MageWorx_CustomerCredit_Model_Rules::CC_RULE_TYPE_GIVE);
        foreach ($ruleModel->getData() as $rule) {
            $handler->execute($rule);
        }
        $this->sendNewPointsEmail($order);
        return $this;
    }

}