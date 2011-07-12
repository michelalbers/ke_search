<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2010 Andreas Kiefer (kennziffer.com) <kiefer@kennziffer.com>
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

require_once(t3lib_extMgm::extPath('ke_search').'lib/class.tx_kesearch_lib.php');

/**
 * Plugin 'Faceted search - searchbox and filters' for the 'ke_search' extension.
 *
 * @author	Andreas Kiefer (kennziffer.com) <kiefer@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kesearch
 */
class tx_kesearch_pi3 extends tx_kesearch_lib {
	var $scriptRelPath      = 'pi1/class.tx_kesearch_pi1.php';	// Path to this script relative to the extension dir.

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content, $conf) {

		$this->ms = t3lib_div::milliseconds();
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_USER_INT_obj = 1;	// Configuring so caching is not expected. This value means that no cHash params are ever set. We do this, because it's a USER_INT object!

		// initializes plugin configuration
		$this->init();

		if ($this->conf['renderMethod'] != 'static') $this->initXajax();

		// hook for initials
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['initials'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['initials'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$_procObj->addInitials($this);
			}
		}

		// get templates
		$template['multiselect'] = $this->cObj->getSubpart($this->templateCode, '###SUB_FILTER_MULTISELECT###');
		$template['multifilter'] = $this->cObj->getSubpart($template['multiselect'], '###SUB_FILTER_MULTISELECT_FILTER###');
		$template['multioption'] = $this->cObj->getSubpart($template['multifilter'], '###SUB_FILTER_MULTISELECT_OPTION###');

		// get defined filters from FlexForm
		$filters = $this->getFiltersFromFlexform();

		foreach($filters as $filter) {
			$contentOptions = '';
			$countLoops = 1;
			$this->piVars['filter'][$filter['uid']] = array_unique($this->piVars['filter'][$filter['uid']]);
			$options = $this->getFilterOptions($filter['options']);
			foreach($options as $optionKey => $option) {
				$selected = ($this->piVars['filter'][$filter['uid']][$optionKey]) ? 'checked="checked"' : '';
				$markerArray['###ADDCLASS###'] = ($countLoops%3) ? '' : ' last';
				$markerArray['###FILTERNAME###'] = 'tx_kesearch_pi1[filter][' . $filter['uid'] . ']';
				$markerArray['###OPTIONID###'] = $option['uid'];
				$markerArray['###OPTIONKEY###'] = $optionKey;
				$markerArray['###OPTIONTITLE###'] = $option['title'];
				$markerArray['###OPTIONTAG###'] = $option['tag'];
				$markerArray['###SELECTED###'] = $selected;
				$countLoops++;
				$contentOptions .= $this->cObj->substituteMarkerArray($template['multioption'], $markerArray);
			}
			$content .= $this->cObj->substituteSubpart(
				$template['multifilter'],
				'###SUB_FILTER_MULTISELECT_OPTION###',
				$contentOptions
			);
			$content = $this->cObj->substituteMarker(
				$content,
				'###TITLE###',
				$filter['title']
			);
		}
		$content = $this->cObj->substituteSubpart(
			$template['multiselect'],
			'###SUB_FILTER_MULTISELECT_FILTER###',
			$content
		);
		$content = $this->cObj->substituteMarker(
			$content,
			'###FORM_ACTION###',
			$this->pi_getPageLink($GLOBALS['TSFE']->id)
		);
		$content = $this->cObj->substituteMarker($content, '###PAGEID###', $this->conf['resultPage']);

		return $this->pi_wrapInBaseClass($content);
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_search/pi2/class.tx_kesearch_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_search/pi2/class.tx_kesearch_pi2.php']);
}
?>
