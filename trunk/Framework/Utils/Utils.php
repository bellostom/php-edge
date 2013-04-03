<?php
namespace Framework\Core\Utils;

class Utils
{
	public static function genRandom($length=10)
	{
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$return   = '';

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

	public static function dateTimeFormat($date, $timezone='GMT')
	{
		$context = Context::getInstance();
		$lang = Language::getLanguageById($context->session->lang);
		$fromTimezone = new DateTimeZone($timezone);
		$datetime = new DateTime($date, $fromTimezone);
		$toTimezone = new DateTimeZone($lang->timezone);
		$datetime->setTimezone($toTimezone);
		$strings = $lang->getStrings();
		$stamp = $datetime->getTimeStamp();
		return static::format($stamp, $strings->DATETIME_FORMAT);
	}

	public static function objectToArray( $object )
    {
        if( !is_object( $object ) && !is_array( $object ) )
        {
            return $object;
        }
        if( is_object( $object ) )
        {
            $object = get_object_vars( $object );
        }
        return array_map(array('Utils','objectToArray'), $object );
    }

    public static function removeNewLine($subject)
    {
    	return preg_replace("/[\n\r]/","",$subject);
    }

	public static function isIpInNetwork($ip, $net_addr, $net_mask)
	{
		if($net_mask <= 0)
			return false;
        $ip_binary_string = sprintf("%032b",ip2long($ip));
        $net_binary_string = sprintf("%032b",ip2long($net_addr));
        return (substr_compare($ip_binary_string,$net_binary_string,0,$net_mask) === 0);
	}

}
?>