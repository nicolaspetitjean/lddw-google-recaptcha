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

$class_path = __DIR__ . '/classes/';
require_once($class_path . 'LddwHelper.php');
require_once($class_path . 'recaptchalib.php');