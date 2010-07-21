<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Rueegg Tuck Partner (t3@rtpartner.ch)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Utility methods for the 'rtp_clearcacherequest' extension.
 *
 * @author	Simon Tuck <stu@rtpartner.ch>
 * @package	TYPO3
 * @subpackage	tx_rtpclearcacherequest
 */
 

class tx_rtpclearcacherequest_div 
{
	private static $_config		= null;
	
	const EXT_KEY				= 'rtp_clearcacherequest';
	const WILDCARD				= '*';
	
	/**
	 * @return array
	 */
	public static function getConfig()
	{
		if(is_null(self::$_config)) {
			self::$_config = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][self::EXT_KEY] 
							= unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::EXT_KEY]);	
		}
		return self::$_config;
	}

	/**
	 * @param string $key
	 * @return mixed
	 */	
	public static function getConfigValue($key)
	{
		static $_config = null;		
		if(is_null($_config)) $_config = self::getConfig();
		return $_config[$key];
	}

	/**
	 * Splits a comma separated list into an array of unique 
	 * integers or an array containing the wildcard character. 
	 *  
	 * @param string $list		
	 * @return array|boolean	
	 */	
	public static function toIdArray($list)
	{
		$array = self::toArray(',', $list);	
		if(in_array(self::WILDCARD, $array)) {
			$idArray[] = self::WILDCARD;
		} else {
			$idArray = array_unique(array_filter(array_map('intval', $array)));	
		}		
		return !empty($idArray) ? $idArray : false;
	}
	
	/**
	 * Splits a string by a string into an array and 
	 * removes empty values from the resulting array.
	 * 
	 * @param string 		$delim delimiter to split the string by
	 * @param string 		$str string to be split into an array
	 * @return array
	 */
	public static function toArray($delim, $str)
	{
		return array_filter(array_map('trim', explode($delim, $str)), 'strlen');
	}	
	
	/**
	 * @param array $pages
	 * @return boolean
	 */
	public static function hasWildcard(array $pages)
	{
		return in_array(self::WILDCARD, $pages);
	}	
    
	public static function throwPageNotFound()
	{
		$msg = 'The requested page does not exist!';
		if ($GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling'])	{
			$GLOBALS['TSFE']->pageNotFoundAndExit($msg);
		} else {
			header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
			$GLOBALS['TSFE']->printError($msg);
			exit;
		}		
	}	
	
    /**
     * @param string		$msg Message to send to the developer's log
     * @param boolean 		$warning Sets the severity to "warning" when true		
     */
    public static function log($msg, $warning = false)
    {    
		if(self::_hasLog()) t3lib_div::devLog($msg, self::EXT_KEY, ($warning ? 0 : 2));
    }

    /**
     * @return boolean
     */
    private static function _hasLog()
    {
		return self::getConfigValue('devLog') ? true : false;   	
    }    
}