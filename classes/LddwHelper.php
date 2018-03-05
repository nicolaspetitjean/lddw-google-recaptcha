<?php
/**
 * 2018 http://www.la-dame-du-web.com
 *
 * @author Nicolas PETITJEAN <n.petitjean@la-dame-du-web.com>
 * @copyright 2018 Nicolas PETITJEAN
 * @license MIT License
 */

class LddwHelper
{
    static public function getValueMultilang($key)
    {
        $languages = Language::getLanguages();
        $results_array = array();
        foreach($languages as $language) {
            $results_array[$language['id_lang']] = Tools::getValue($key . '_' . $language['id_lang'], Configuration::get($key, $language['id_lang']));
        }

        return $results_array;
    }

    static public function getConfigMultilang($key)
    {
        $languages = Language::getLanguages();
        $results_array = array();
        foreach($languages as $language) {
            $results_array[$language['id_lang']] = Configuration::get($key, $language['id_lang']);
        }

        return $results_array;
    }

    static public function validateNotEmpty($values)
    {
        if(!is_array($values)) {
            $values = array($values);
        }

        $result = true;
        foreach($values as $value) {
            if(empty($value)) {
                $result = false;
                break;
            }
        }

        return $result;
    }
}