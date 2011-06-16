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

require_once(t3lib_extMgm::extPath('ke_search').'indexer/class.tx_kesearch_indexer_types.php');

/**
 * Plugin 'Faceted search' for the 'ke_search' extension.
 *
 * @author	Andreas Kiefer (kennziffer.com) <kiefer@kennziffer.com>
 * @author	Stefan Froemken (kennziffer.com) <froemken@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kesearch
 */
class tx_kesearch_indexer_types_ttnews extends tx_kesearch_indexer_types {

	/**
	 * Initializes indexer for tt_news
	 */
	public function __construct($pObj) {
		parent::__construct($pObj);
	}


	/**
	 * This function was called from indexer object and saves content to index table
	 *
	 * @return string content which will be displayed in backend
	 */
	public function startIndexing() {
		$content = '';

			// get all the tt_news entries to index
			// don't index hidden or deleted news, BUT
			// get the news with frontend user group access restrictions
			// or time (start / stop) restrictions.
			// Copy those restrictions to the index.
		$fields = '*';
		$table = 'tt_news';
		$where = 'pid IN (' . $this->indexerConfig['sysfolder'] . ') AND hidden = 0 AND deleted = 0';
		$groupBy = '';
		$orderBy = '';
		$limit = '';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,$table,$where,$groupBy,$orderBy,$limit);
		$resCount = $GLOBALS['TYPO3_DB']->sql_num_rows($res);
		if ($resCount) {
			while ( ($newsRecord = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) ) {

					// compile the information which should go into the index
				$title = strip_tags($newsRecord['title']);
				$abstract = strip_tags($newsRecord['short']);
				$content = strip_tags($newsRecord['bodytext']);
				$fullContent = $abstract . "\n" . $content;
				$params = '&tx_ttnews[tt_news]=' . $newsRecord['uid'];
				$tags = '';
				$additionalFields = array();

				// hook for custom modifications of the indexed data, e. g. the tags
				if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyNewsIndexEntry'])) {
					foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyNewsIndexEntry'] as $_classRef) {
						$_procObj = & t3lib_div::getUserObj($_classRef);
						$_procObj->modifyNewsIndexEntry(
							$title,
							$abstract,
							$fullContent,
							$params,
							$tags,
							$newsRecord,
							$additionalFields
						);
					}
				}
				$title = '';

				// ... and store them
				$this->pObj->storeInIndex(
					$this->indexerConfig['storagepid'],    // storage PID
					$title,                          // page title
					'',                       // content type
					$this->indexerConfig['targetpid'],     // target PID: where is the single view?
					$fullContent,                    // indexed content, includes the title (linebreak after title)
					$tags,                           // tags
					$params,                         // typolink params for singleview
					$abstract,                       // abstract
					$newsRecord['sys_language_uid'], // language uid
					$newsRecord['starttime'],        // starttime
					$newsRecord['endtime'],          // endtime
					$newsRecord['fe_group'],         // fe_group
					false,                           // debug only?
					$additionalFields                // additional fields added by hooks
				);
			}
			$content = '<p><b>Indexer "' . $this->indexerConfig['title'] . '": ' . $resCount . ' News have been indexed.</b></p>'."\n";
		}
		return $content;
	}
}
