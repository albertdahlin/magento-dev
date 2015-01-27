<?php
$version = substr(Mage::getVersion(), 0, 3);

switch ($version) {
    case "1.9":
        include('session-original191.php');
        break;
    case "1.7":
    default:
        include('session-original172.php');
        break;
}

class Mage_Admin_Model_Session
 extends original_mage_admin_model_session
{
    /**
     * Log in automatically to admin
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        $conf       = dahl_dev::getConfig();
        $userName   = $conf->getData('auto_login/username');
        $mode       = $conf->getData('auto_login/mode');

        if ($mode != 'auto' || !$userName) {
            return parent::isLoggedIn();
        }

        $loggedIn = $this->getUser() && $this->getUser()->getId();

        if (!$loggedIn) {
            $userModel = Mage::getModel('admin/user');
            $user = $userModel->loadByUserName($userName);
            $this->setUser($user);
            $this->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
        }

        return parent::isLoggedIn();
    }

    /**
     * Try to login user in admin
     *
     * @param  string $username
     * @param  string $password
     * @param  Mage_Core_Controller_Request_Http $request
     * @return Mage_Admin_Model_User|null
     */
    public function login($username, $password, $request = null)
    {
        $conf       = dahl_dev::getConfig();
        $mode       = $conf->getData('auto_login/mode');

        if ($mode != 'no_password') {
            return parent::login($username, $password, $request);
        }

        if (empty($username) || empty($password)) {
            return;
        }

        try {
            /** @var $user Mage_Admin_Model_User */
            $user = $this->_factory->getModel('admin/user');
            $user->login($username, $password);
            if ($user->getId()) {
                $this->renewSession();

                if (Mage::getSingleton('adminhtml/url')->useSecretKey()) {
                    Mage::getSingleton('adminhtml/url')->renewSecretUrls();
                }
                $this->setIsFirstPageAfterLogin(true);
                $this->setUser($user);
                $this->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());

                $alternativeUrl = $this->_getRequestUri($request);
                $redirectUrl = $this->_urlPolicy->getRedirectUrl($user, $request, $alternativeUrl);
                if ($redirectUrl) {
                    Mage::dispatchEvent('admin_session_user_login_success', array('user' => $user));
                    $this->_response->clearHeaders()
                        ->setRedirect($redirectUrl)
                        ->sendHeadersAndExit();
                }
            } else {
                Mage::throwException(Mage::helper('adminhtml')->__('Invalid User Name or Password.'));
            }
        } catch (Mage_Core_Exception $e) {
            $userModel = Mage::getModel('admin/user');
            $user = $userModel->loadByUserName($userName);

            if ($user) {
                $this->setUser($user);
                $this->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
                return $user;
            } else {
                Mage::dispatchEvent('admin_session_user_login_failed',
                    array('user_name' => $username, 'exception' => $e));
                if ($request && !$request->getParam('messageSent')) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                    $request->setParam('messageSent', true);
                }
            }
        }

        return $user;
    }
}
