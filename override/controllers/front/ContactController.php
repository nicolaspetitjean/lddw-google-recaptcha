<?php
/**
 * 2018 http://www.la-dame-du-web.com
 *
 * @author Nicolas PETITJEAN <n.petitjean@la-dame-du-web.com>
 * @copyright 2018 Nicolas PETITJEAN
 * @license MIT License
 */

class ContactController extends ContactControllerCore
{
    /**
     * Check if the controller is available for the current user/visitor
     */
    public function checkAccess()
    {
        if(!Module::isInstalled('lddw_grecaptcha')) {
            return parent::checkAccess();
        }
        $valid = Hook::exec('onSubmitContactForm');
        if(empty($valid)) {
            $this->errors[] = Tools::displayError('Invalid ReCaptcha Response');
        }

        return $valid;
    }

    /**
     * Assigns Smarty variables when access is forbidden
     */
    public function initCursedPage() {
        parent::setMedia();
        parent::initHeader();
        parent::initContent();
        parent::initFooter();
        parent::display();
        die();
    }
}
