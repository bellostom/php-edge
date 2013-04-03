<?php
namespace Framework\Models;

use Framework\Core\Settings;

class Language extends Identifiable {
	private static $lang = null;
	private static $langs = null;
	public static $table = array(
	  "table" => "countries",
	  "PK" => array(
		"id" => null
		)
	 );
	public $timezone;
	public $keyword_extraction;
	public $display_tags;
	public $combo;

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

	/**
	 *
	 * Get all languages and cache them
	 * for the request's duration
	 * @param int $cms_only Return cms only langs or not
	 */
	public static function getAllLanguages() {
		if(is_null(Language::$langs)) {
			$context = Context::getInstance();
			$oLang = Language::getLanguageById($context->session->lang);
			$order = sprintf("(CASE WHEN id='%s' THEN 0 ELSE 1 END)", $oLang->id);
			$q = sprintf("SELECT * FROM countries ORDER BY %s", $order);
			Language::$langs = forward_static_call(array('Table', 'query'), $q, true);
		}
		return Language::$langs;
	}

	public static function getAllActiveLanguages() {
		$q = "SELECT * FROM countries WHERE combo='1'";
		return parent::query($q);
	}
}
?>