<?php
namespace Edge\Models;

use Edge\Core\Settings;

class Language extends Identifiable {
	private static $lang = null;

    protected static $_members = array(
        'timezone'
    );

	/**
	 *
	 * Get the language's internationalizing
	 * string tokens
	 */
	public function getStrings() {
		$settings = Settings::getInstance();
		$path = sprintf("%s/%s.php", $settings->i18n_dir, $this->id);
		include($path);
		return $LANGUAGE;
	}

	/**
	 *
	 * Get the language by id and cache it
	 * for the request's duration
	 * @param $id
	 */
	public static function getLanguageById($id)	{
		if(is_null(Language::$lang) || (Language::$lang->id != $id)) {
			$lang = parent::getItemById($id);
			Language::$lang = $lang;
		}
		return Language::$lang;
	}

    public static function getPk(){
        return array("id");
    }

    public static function getTable(){
        return 'language';
    }
}