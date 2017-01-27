<?php
require_once "Mage/Newsletter/controllers/SubscriberController.php";
/**
 * Created by PhpStorm.
 * User: riterrani
 * Date: 11/28/16
 * Time: 5:01 PM
 */
class Entangled_Custom_Rewrite_SubscriberController extends Mage_Core_Controller_Front_Action {
    /**
     * New subscription action
     */
    public function newAction()
    {
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $session            = Mage::getSingleton('core/session');
            $customerSession    = Mage::getSingleton('customer/session');
            $email              = (string) $this->getRequest()->getPost('email');

            try {
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 &&
                    !$customerSession->isLoggedIn()) {
                    Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                }

                $ownerId = Mage::getModel('customer/customer')
                    ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                    ->loadByEmail($email)
                    ->getId();
//                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
//                    Mage::throwException($this->__('This email address is already assigned to another user.'));
//                }

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $session->addSuccess($this->__('Confirmation request has been sent.'));
                }
                else {
                    $session->addSuccess($this->__('Thank you for your subscription.'));
                }
            }
            catch (Mage_Core_Exception $e) {
                if(strpos($e->getMessage(),"assigned to another") !== false){
                    $session->addException($e, $this->__('It appears this email address is already registered on the site. If this is you, please <a href="%s">log in</a> before trying to follow an author.', Mage::getUrl("customer/account")));
                }else{
                    $session->addException($e, $this->__('There was a problem with the subscription: %s', $e->getMessage()));
                }
            }
            catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription.'));
            }
        }
        $this->_redirectReferer();
    }
}