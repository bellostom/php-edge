<?php
namespace Edge\Utils;

class Utils{

	public static function genRandom($length=10){
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$return = '';

		if ($length > 0) {
			$totalChars = strlen($characters) - 1;
			for ($i = 0; $i <= $length; ++$i) {
				$return .= $characters[rand(0, $totalChars)];
			}
		}
  		return $return;
	}

	public static function format($timestamp, $format){
		$context = Context::getInstance();
		$lang = Language::getLanguageById($context->session->lang);
		$strings = $lang->getStrings();
		$month = mb_substr($strings->MONTH_NAMES[date('n', $timestamp)], 0, 3, "UTF-8");
		$vals = array(
			'%d' => $strings->DAY_NAMES[date('N', $timestamp)],
			'%D' => date('d', $timestamp),
			'%M' => $month,
			'%m' => $strings->MONTH_NAMES[date('n', $timestamp)],
			'%Y' => date('Y', $timestamp),
			'%H' => date('H', $timestamp),
			'%i' => date('i', $timestamp),
			'%s' => date('s', $timestamp),
			'%Z' => date('P', $timestamp)
		);
		return str_replace(array_keys($vals), array_values($vals), $format);
	}

	public static function dateTimeFormat($date, $timezone='GMT'){
		$context = Context::getInstance();
		$lang = Language::getLanguageById($context->session->lang);
		$fromTimezone = new \DateTimeZone($timezone);
		$datetime = new \DateTime($date, $fromTimezone);
		$toTimezone = new \DateTimeZone($lang->timezone);
		$datetime->setTimezone($toTimezone);
		$strings = $lang->getStrings();
		$stamp = $datetime->getTimeStamp();
		return static::format($stamp, $strings->DATETIME_FORMAT);
	}

	/**
     * warning requires `yum -y install php-intl`
	 * for transliterator to work
     * @param $text
     *
     * @return mixed|string
     */
    public static function slugify($text){
		static $transliteratorExists;
        if(empty($text)){
            return 'n-a';
        }
		if(is_null($transliteratorExists)){
			$transliteratorExists = (function_exists('transliterator_transliterate') && $transliterator = \Transliterator::create("Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC; Lower();") !== null);
		}
		if($transliteratorExists === true){
	        return preg_replace('#[ -]+#', '-',transliterator_transliterate('Any-Latin; Latin-ASCII; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();',$text));
		}
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);
        $text = trim($text, '-');
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        if(empty($text)){
            return 'n-a';
        }
        return $text;
    }
}