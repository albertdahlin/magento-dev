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
        if (!isset($_SERVER['AUTOLOGIN_ADMIN'])) {
            return parent::isLoggedIn();
        }

        $loggedIn = $this->getUser() && $this->getUser()->getId();

        if (!$loggedIn) {
            $session = Mage::getModel('admin/user');
            $user = $session->loadByUserName($_SERVER['AUTOLOGIN_ADMIN']);
            $this->setUser($user);
            $this->setAcl(Mage::getResourceModel('admin/acl')->loadAcl());
        }

        return parent::isLoggedIn();
    }
}
