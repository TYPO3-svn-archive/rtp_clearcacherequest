<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Simon Tuck <stu@rtpartner.ch>
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
 * Plugin 'clearcacherequest' for the 'rtp_clearcacherequest' extension.
 *
 * @author	Simon Tuck <stu@rtpartner.ch>
 * @package	TYPO3
 * @subpackage	tx_rtpclearcacherequest
 */
class tx_rtpclearcacherequest_pi1
{
	private static $_tce			= null;
	private static $_enabledPages	= null;
	private $_requestedPages		= null;
	private $_clearCachePages		= null;
	
	public function clearCacheRequest($content, $conf)	{

		// 
		if($this->_isValidRequest()) {
			$this->_clearPageCache();
		} elseif(self::_isPluginPage()) {
			tx_rtpclearcacherequest_div::log('Invalid clear cache request received!', true);
			tx_rtpclearcacherequest_div::throwPageNotFound();
		}	
	}
	
	protected function _clearPageCache()
	{
		if(tx_rtpclearcacherequest_div::hasWildcard($this->_getClearCachePages())) {
			// Clears entire page cache when wildcard has been set!
			self::_getTce()->internal_clearPageCache();
			tx_rtpclearcacherequest_div::log('Cache was cleared for all pages!');
		} else {
			foreach($this->_getClearCachePages() as $pageId) {
				self::_getTce()->clear_cacheCmd($pageId);
			}
			tx_rtpclearcacherequest_div::log('Cache was cleared for page(s): ' . implode(', ', $this->_getClearCachePages()));
		}
	}
	
	/**
	 * @return boolean
	 */
	protected function _isValidRequest()
	{
		return 	self::_isPluginPage()
				&& self::_isValidKey()
				&& self::_isValidIp()
				&& $this->_hasClearCachePages();
	}
	
	/**
	 * @return boolean
	 */
	private static function _isValidIp()
	{
		$enabledIps = tx_rtpclearcacherequest_div::getConfigValue('enabledIps');
		return t3lib_div::cmpIP(t3lib_div::getIndpEnv('REMOTE_ADDR'), $enabledIps);
	}
	
	/**
	 * @return boolean
	 */
	private static function _isValidKey()
	{
		$secretKey = tx_rtpclearcacherequest_div::getConfigValue('secretKey');
		return $secretKey && strcmp(t3lib_div::_GET('secretKey'), sha1($secretKey)) === 0;
	}
	
	/**
	 * @return boolean
	 */
	private static function _isPluginPage()
	{
		$pluginPage = intval(tx_rtpclearcacherequest_div::getConfigValue('pluginPage'));
		$currentPage = intval($GLOBALS['TSFE']->id);
		return $currentPage > 0 && $currentPage === $pluginPage;
	}
	
	/**
	 * @return boolean
	 */
	private function _hasClearCachePages()
	{
		return is_array($this->_getClearCachePages()) && count($this->_getClearCachePages()) ? true : false;
	}
	
	/**
	 * @return array|boolean
	 */
	private function _getClearCachePages()
	{
		if(is_null($this->_clearCachePages)) {
			if($this->_hasRequestedPages() && self::_hasEnabledPages()) {
				if(tx_rtpclearcacherequest_div::hasWildcard(self::_getEnabledPages())) {
					// Wildcard enables the clear cache command for any and all pages
					$this->_clearCachePages = $this->_getRequestedPages();
				} else {
					$this->_clearCachePages = array_intersect($this->_getRequestedPages(), self::_getEnabledPages());	
				}
			} else {
				$this->_clearCachePages = false;
			}
		}
		return $this->_clearCachePages;
	}
	
	/**
	 * @return array|boolean
	 */
	private function _getRequestedPages()
	{
		if(is_null($this->_requestedPages)) {
			$this->_requestedPages = tx_rtpclearcacherequest_div::toIdArray(t3lib_div::_GET('clearPageCache'));				
		}
		return $this->_requestedPages;
	}
	
	/**
	 * @return boolean
	 */	
	private function _hasRequestedPages()
	{
		return is_array($this->_getRequestedPages()) && count($this->_getRequestedPages());
	}

	/**
	 * @return array|boolean 
	 */
	private static function _getEnabledPages()
	{
		if(is_null(self::$_enabledPages)) {
			$enabledPages = tx_rtpclearcacherequest_div::getConfigValue('enabledPages');
			self::$_enabledPages = tx_rtpclearcacherequest_div::toIdArray($enabledPages);			
		}
		return self::$_enabledPages;
	}
	
	/**
	 * @return boolean
	 */	
	private static function _hasEnabledPages()
	{
		return is_array(self::_getEnabledPages()) && count(self::_getEnabledPages());
	}
		
	/**
	 * @return t3lib_TCEmain
	 */
	private static function _getTce()
	{
		if(!(self::$_tce instanceof t3lib_TCEmain)) {
			self::$_tce = t3lib_div::makeInstance('t3lib_TCEmain');	
		}
		return self::$_tce;
	}
}