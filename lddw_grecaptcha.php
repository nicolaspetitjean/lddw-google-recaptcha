<?php
/**
 * 2018 http://www.la-dame-du-web.com
 *
 * @author Nicolas PETITJEAN <n.petitjean@la-dame-du-web.com>
 * @copyright 2018 Nicolas PETITJEAN
 * @license MIT License
 */

if(!defined('_PS_VERSION_')) {
    exit;
}

require_once(__DIR__ . '/classloader.php');

class lddw_grecaptcha extends Module
{
    private $fields;

    public function __construct()
    {
        $this->name = 'lddw_grecaptcha';
        $this->tab = 'front_office_features';
        $this->version = '1.0.1';
        $this->author = 'Nicolas PETITJEAN';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.18');
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Google ReCaptcha');
        $this->description = $this->l('Add the Google ReCaptcha fonctionality in contact form.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
        $this->buildFields();
    }

    public function getFields()
    {
        return $this->fields;
    }

    private function buildFields()
    {
        $this->fields = array(
            'legend' => array(
                'title' => $this->l('Settings'),
            ),
            'input'  => array(
                array(
                    'type'     => 'select',
                    'desc'     => 'Select the recaptcha color scheme.',
                    'label'    => $this->l('ReCaptcha Style'),
                    'name'     => 'LDDW_GR_STYLE',
                    'required' => true,
                    'lang'     => false,
                    'options'  => array(
                        'query' => array(
                            array('id' => 'dark', 'name' => $this->l('Dark')),
                            array('id' => 'light', 'name' => $this->l('Light'))
                        ),
                        'id'    => 'id',
                        'name'  => 'name',
                    )
                ),
                array(
                    'type'     => 'text',
                    'label'    => $this->l('ReCaptcha Site Key'),
                    'name'     => 'LDDW_GR_SITE_KEY',
                    'required' => true,
                    'lang'     => false,
                ),
                array(
                    'type'     => 'text',
                    'label'    => $this->l('ReCaptcha Secret Key'),
                    'name'     => 'LDDW_GR_SECRET_KEY',
                    'required' => true,
                    'lang'     => false,
                )
            ),
            'submit' => array(
                'title' => $this->l('Save'),
                'class' => 'btn btn-default pull-right'
            )
        );
    }

    public function install()
    {
        if(Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        $succeed = parent::install() &&
            $this->registerHook(array(
                'displayFooter',
                'header',
                'onSubmitContactForm'
            ));

        return $succeed;
    }

    public function uninstall()
    {
        if(!parent::uninstall()
            || !Configuration::deleteByName('LDDW_GR_STYLE')
            || !Configuration::deleteByName('LDDW_GR_SITE_KEY')
            || !Configuration::deleteByName('LDDW_GR_SECRET_KEY')
        ) {
            return false;
        }

        return true;
    }

    public function getConfigValues()
    {
        $fields = $this->getFields();
        $return = [];
        foreach($fields['input'] as $field) {
            if($this->isFieldMultilang($field)) {
                $return[$field['name']] = LddwHelper::getConfigMultilang($field['name']);
            } else {
                $return[$field['name']] = Configuration::get($field['name']);
            }
        }

        return $return;
    }

    public function getSubmitedValues()
    {
        $fields = $this->getFields();
        $return = [];
        foreach($fields['input'] as $field) {
            if($this->isFieldMultilang($field)) {
                $return[$field['name']] = LddwHelper::getValueMultilang($field['name']);
            } else {
                $return[$field['name']] = Tools::getValue($field['name'], Configuration::get($field['name']));
            }
        }

        return $return;
    }

    public function isFieldMultilang($field)
    {
        return isset($field['lang']) && $field['lang'] ? true : false;
    }

    public function hookDisplayFooter()
    {
        if($this->context->controller->php_self != 'contact') {
            return;
        }

        $config = $this->getConfigValues();
        $iso_code = $this->context->language->iso_code;
        $js = "<script>var LDDW_GR_CONFIG = {sitekey:'${config['LDDW_GR_SITE_KEY']}',theme:'${config['LDDW_GR_STYLE']}'};</script>";
        $js .= '<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit&hl=' . $iso_code . '" async defer></script>';

        return $js;
    }

    public function hookHeader()
    {
        if($this->context->controller->php_self != 'contact') {
            return;
        }
        $this->context->controller->addCSS(($this->_path) . 'lddw_grecaptcha.css', 'all');
        $this->context->controller->addJS($this->_path . 'js/lddw_grecaptcha.js');
        //$this->context->controller->registerJavascript('modules-js-lddw-cookies-law', 'modules/' . $this->name . '/js/lddw_cookieslaw.js', ['media' => 'all', 'priority' => 150]);
        //$this->context->controller->registerStylesheet('modules-css-lddw-cookies-law', 'modules/' . $this->name . '/css/lddw_cookieslaw.css', ['media' => 'all', 'priority' => 150]);
    }

    public function hookOnSubmitContactForm()
    {
        if(!Tools::isSubmit('submitMessage')) {
            return true;
        }
        $config = $this->getConfigValues();
        $re_captcha_value = Tools::getValue('g-recaptcha-response');

        // Instantiate ReCaptcha object
        $re_captcha = new ReCaptcha($config['LDDW_GR_SECRET_KEY']);

        // Validate
        $re_captcha_response = $re_captcha->verifyResponse($_SERVER['REMOTE_ADDR'], $re_captcha_value);

        return !($re_captcha_response == null || !$re_captcha_response->success);
    }

    public function getContent()
    {
        $output = '';

        if(Tools::isSubmit('submit' . $this->name)) {
            $errors = array();
            $submitedValues = $this->getSubmitedValues();

            // Validate site key
            $LDDW_GR_SITE_KEY = $submitedValues['LDDW_GR_SITE_KEY'];
            if(!LddwHelper::validateNotEmpty($LDDW_GR_SITE_KEY)) {
                $errors[] = $this->l('Site key can\'t be empty.');
            }

            // Validate secret key
            $LDDW_GR_SECRET_KEY = $submitedValues['LDDW_GR_SECRET_KEY'];
            if(!LddwHelper::validateNotEmpty($LDDW_GR_SECRET_KEY)) {
                $errors[] = $this->l('Secret key can\'t be empty.');
            }

            // Update.. or not !
            if(empty($errors)) {
                foreach($submitedValues as $submitedKey => $submitedValue) {
                    Configuration::updateValue($submitedKey, $submitedValue);
                }
                $output = $this->displayConfirmation($this->l('Settings updated'));
            } else {
                $output = $this->displayError(implode("<br />", $errors));
            }

            if(Configuration::get('PS_DISABLE_OVERRIDES')) {
                $output .= $this->displayError(implode("<br />", array($this->l('This module require option "Disable overrides" to be inactive.'))));
            }

        }

        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        $output = '';
        if(Configuration::get('PS_DISABLE_OVERRIDES')) {
            $output .= $this->displayError(implode("<br />", array($this->l('This module require option "Disable overrides" to be inactive.'))));
        }
        // Get default language
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT');

        // Init Fields form array
        $fields_form[0]['form'] = $this->fields;

        $helper = new HelperForm();

        // Module, token and currentIndex
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        // Language
        $helper->default_form_language = $default_lang;
        $helper->allow_employee_form_lang = $default_lang;

        // Title and toolbar
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;        // false -> remove toolbar
        $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
        $helper->submit_action = 'submit' . $this->name;
        $helper->toolbar_btn = array(
            'save' =>
                array(
                    'desc' => $this->l('Save'),
                    'href' => AdminController::$currentIndex . '&configure=' . $this->name . '&save' . $this->name .
                        '&token=' . Tools::getAdminTokenLite('AdminModules'),
                ),
            'back' => array(
                'href' => AdminController::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminModules'),
                'desc' => $this->l('Back to list')
            )
        );

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigValues(),
            'languages'    => $this->context->controller->getLanguages(),
            'id_language'  => $this->context->language->id
        );
        $output .= $helper->generateForm($fields_form);

        return $output;
    }
}