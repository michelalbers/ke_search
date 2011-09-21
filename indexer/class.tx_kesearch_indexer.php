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


/**
 * Plugin 'Faceted search' for the 'ke_search' extension.
 *
 * @author	Andreas Kiefer (kennziffer.com) <kiefer@kennziffer.com>
 * @package	TYPO3
 * @subpackage	tx_kesearch
 */
class tx_kesearch_indexer {
	var $counter;
	var $extConf; // extension configuration
	var $indexerConfig = array(); // saves the indexer configuration of current loop
	var $lockFile = '';
	var $additionalFields = '';
	var $indexingErrors = array();
	var $startTime;
	var $currentRow = array(); // current row which have to be inserted/updated to database
	
	/**
	 * @var t3lib_Registry
	 */
	var $registry;


	/**
	 * Constructor of this class
	 */
	public function __construct() {
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_search']);
		// sphinx has problems with # in query string.
		// so you have the possibility to change # against some other char
		if(t3lib_extMgm::isLoaded('ke_search_premium')) {
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_search_premium']);
			if(!$extConf['prePostTagChar']) $extConf['prePostTagChar'] = '_';
			$this->extConf['prePostTagChar'] = $extConf['prePostTagChar'];
		} else {
			// MySQL has problems also with #
			// but we have wrapped # with " and it works.
			$this->extConf['prePostTagChar'] = '#';
		}
		$this->registry = t3lib_div::makeInstance('t3lib_Registry');
		$this->lockFile = PATH_site . 'typo3temp/ke_search_indexer.lock';
	}

	/*
	 * function startIndexing
	 * @param $verbose boolean 	if set, information about the indexing process is returned, otherwise processing is quiet
	 * @param $extConf array			extension config array from EXT Manager
	 * @param $mode string				"CLI" if called from command line, otherwise empty
	 * @return string							only if param $verbose is true
	 */
	function startIndexing($verbose=true, $extConf, $mode='')  {
		// write starting timestamp into temp file
		// this is a little helper for clean up process
		// delete all records which are older than starting timestamp in temp file
		$this->startTime = time() . CHR(10);
		t3lib_div::unlink_tempfile($this->lockFile);
		t3lib_div::writeFileToTypo3tempDir($this->lockFile, $this->startTime);

		// get configurations
		$configurations = $this->getConfigurations();

		// register additional fields which should be written to DB
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['registerAdditionalFields'])) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['registerAdditionalFields'] as $_classRef) {
				$_procObj = & t3lib_div::getUserObj($_classRef);
				$_procObj->registerAdditionalFields($this->additionalFields);
			}
		}

		foreach($this->additionalFields as $value) {
			$addUpdateQuery .= ', ' . $value[0] . ' = ?';
			$addInsertQueryFields .= ', ' . $value[0];
			$addInsertQueryValues .= ', ?';
		}

		$GLOBALS['TYPO3_DB']->sql_query('PREPARE searchStmt FROM "
			SELECT *
			FROM tx_kesearch_index
			WHERE orig_uid = ?
			AND pid = ?
			AND type = ?
			LIMIT 1
		"');
		$GLOBALS['TYPO3_DB']->sql_query('PREPARE updateStmt FROM "
			UPDATE tx_kesearch_index
			SET pid=?,
			title=?,
			type=?,
			targetpid=?,
			content=?,
			tags=?,
			params=?,
			abstract=?,
			language=?,
			starttime=?,
			endtime=?,
			fe_group=?,
			tstamp=?' . $addUpdateQuery . '
			WHERE uid=?
		"');
		$GLOBALS['TYPO3_DB']->sql_query('PREPARE updateShortStmt FROM "
			UPDATE tx_kesearch_index
			SET tstamp=?
			WHERE uid=?
		"');
		$GLOBALS['TYPO3_DB']->sql_query('PREPARE insertStmt FROM "
			INSERT ' . $this->extConf['useDelayedForInsert'] . ' INTO tx_kesearch_index
			(pid, title, type, targetpid, content, tags, params, abstract, language, starttime, endtime, fe_group, tstamp, crdate' . $addInsertQueryFields . ')
			VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?' . $addInsertQueryValues . ', ?)
		"');
		$GLOBALS['TYPO3_DB']->sql_query('ALTER TABLE tx_kesearch_index DISABLE KEYS');

		foreach($configurations as $indexerConfig) {
			$this->indexerConfig = $indexerConfig;

			$path = t3lib_extMgm::extPath('ke_search').'indexer/types/class.tx_kesearch_indexer_types_' . $this->indexerConfig['type'] . '.php';
			if(is_file($path)) {
				require_once($path);
				$searchObj = t3lib_div::makeInstance('tx_kesearch_indexer_types_' . $this->indexerConfig['type'], $this);
				$content .= $searchObj->startIndexing();
			}

			// hook for custom indexer
			if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['customIndexer'])) {
				foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['customIndexer'] as $_classRef) {
					$_procObj = & t3lib_div::getUserObj($_classRef);
					$content .= $_procObj->customIndexer($indexerConfig, $this);
				}
			}
		}
		$GLOBALS['TYPO3_DB']->sql_query('ALTER TABLE tx_kesearch_index ENABLE KEYS');
		$GLOBALS['TYPO3_DB']->sql_query('DEALLOCATE PREPARE searchStmt');
		$GLOBALS['TYPO3_DB']->sql_query('DEALLOCATE PREPARE updateStmt');
		$GLOBALS['TYPO3_DB']->sql_query('DEALLOCATE PREPARE insertStmt');
		
		
		// write ending timestamp into temp file
		t3lib_div::unlink_tempfile($this->lockFile);
		t3lib_div::writeFileToTypo3tempDir($this->lockFile, $this->startTime . time());

		// process index cleanup?
		$content .= "\n".'<p><b>Index cleanup processed</b></p>'."\n";
		$content .= $this->cleanUpIndex();


		// print indexing errors
		if (sizeof($this->indexingErrors)) {
			$content .= "\n\n".'<br /><br /><br /><b>INDEXING ERRORS ('.sizeof($this->indexingErrors).')<br /><br />'."\n";
			foreach ($this->indexingErrors as $error) {
				$content .= $error.'<br />'."\n";
			}
		}


		// send notification in CLI mode
		if ($mode == 'CLI') {

			// send finishNotification
			if ($extConf['finishNotification'] && t3lib_div::validEmail($extConf['notificationRecipient'])) {

				// build message
				$msg = 'Indexing process was finished:'."\n";
				$msg .= "==============================\n\n";
				$msg .= strip_tags($content);
				$msg .= "\n\n".'Indexing process ran '.(time()-$this->startTime).' seconds.';

				// send the notification message
				mail($extConf['notificationRecipient'], $extConf['notificationSubject'], $msg);
			}

		}

		// verbose or quiet output? as set in function call!
		if ($verbose) return $content;

	}


	/**
	 * Delete all index elements that are older than starting timestamp in temporary file
	 *
	 * @return string content for BE
	*/
	function cleanUpIndex() {
		$startMicrotime = microtime(true);
		$fileContent = t3lib_div::trimExplode(CHR(10), t3lib_div::getURL($this->lockFile));
		if(count($fileContent) != 2) return; // There must be exactly 2 rows (start and end time)
		foreach($fileContent as $row) {
			if(!intval($row)) { // both rows must contain an integer value
				return;
			}
		}

		$table = 'tx_kesearch_index';
		$where = 'tstamp < ' . $fileContent[0];
		$GLOBALS['TYPO3_DB']->exec_DELETEquery($table, $where);

		// check if ke_search_premium is loaded
		// in this case we have to update sphinx index, too.
		if(t3lib_extMgm::isLoaded('ke_search_premium')) {
			$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['ke_search_premium']);
			if(!$extConf['sphinxIndexerName']) $extConf['sphinxIndexerConf'] = '--all';
			if(is_file($extConf['sphinxIndexerPath']) && is_executable($extConf['sphinxIndexerPath']) && file_exists($extConf['sphinxSearchdPath']) && is_executable($extConf['sphinxIndexerPath'])) {
				$found = preg_match_all('/exec|system/', ini_get('disable_functions'), $match);
				if($found === 0) { // executables are allowed
					$ret = system($extConf['sphinxIndexerPath'] . ' --rotate ' . $extConf['sphinxIndexerName']);
					$content .= $ret;
				} elseif($found === 1) { // one executable is allowed
					if($match[0] == 'system') {
						$ret = system($extConf['sphinxIndexerPath'] . ' --rotate ' . $extConf['sphinxIndexerName']);
					} else { // use exec
						exec($extConf['sphinxIndexerPath'] . ' --rotate ' . $extConf['sphinxIndexerName'], $retArr);
						$ret = implode(';', $retArr);
					}
					$content .= $ret;
				} else {
					$content .= 'Check your php.ini configuration for disable_functions. For now it is not allowed to execute a shell script.';
				}
			} else {
				$content .= 'We can\'t find the sphinx executables or execution permission is missing.';
			}
		}

		// after unlinking this file a new indexer process can be started
		t3lib_div::unlink_tempfile($this->lockFile); // delete lock file from temp direcory

		// calculate duration of indexing process
		$endMicrotime = microtime(true);
		$duration = ceil(($endMicrotime - $startMicrotime) * 1000);
		$content .= '<p><i>Cleanup process took ' . $duration . ' ms.</i></p>'."\n";
		return $content;
	}



	/*
	 * function storeInIndex
	 */
	function storeInIndex($storagepid, $title, $type, $targetpid, $content, $tags='', $params='', $abstract='', $language=0, $starttime=0, $endtime=0, $fe_group, $debugOnly=false, $additionalFields=array()) {
		// check for errors
		$errors = array();
		// no storage pid
		if ($type != 'xtypocommerce' && empty($storagepid)) $errors[] = 'No storage PID set';
		// no title
		if (empty($title)) $errors[] = 'No title set';
		// no type
		if (empty($type)) $errors[] = 'No type set';
		// no target pid
		if (empty($targetpid)) $errors[] = 'No target PID set';

		// collect error messages
		if (sizeof($errors)) {
			$errormessage = '';
			foreach ($errors as $error) {
				$errormessage .= $error.', ';
			}
			$errormessage = trim($errormessage);
			$errormessage = rtrim($errormessage, ',');
			$errormessage .= ' (';
			if (!empty($type)) $errormessage .= 'TYPE: '.$type.'; ';
			if (!empty($params)) $errormessage .= 'PARAMS: '.$params.'; ';
			if (!empty($title)) $errormessage .= 'TITLE: '.$title.'; ';
			if (!empty($targetpid)) $errormessage .= 'TARGET PID: '.$targetpid.'; ';
			if (!empty($storagepid)) $errormessage .= 'STORAGE PID: '.$storagepid.'; ';
			$errormessage .= ')';
			$this->indexingErrors[] = $errormessage;
		}

		$table = 'tx_kesearch_index';
		$now = time();
		$fields_values = array(
			'pid' => intval($storagepid),
			'title' => $title,
			'type' => $type,
			'targetpid' => $targetpid,
			'content' => $content,
			'tags' => $tags,
			'params' => $params,
			'abstract' => $abstract,
			'language' => intval($language),
			'starttime' => intval($starttime),
			'endtime' => intval($endtime),
			'fe_group' => $fe_group,
			'tstamp' => $now,
			'crdate' => $now,
		);

		if(count($additionalFields)) {
			// merge arrays
			$fields_values = array_merge($fields_values, $additionalFields);
		}

		// prepare additional fields for queries
		$this->registry->set('tx_kesearch', 'addFields', $this->additionalFields);
		foreach($this->additionalFields as $value) {
			if($value[1]) { // $value[1] is boolean and means if value is a string
				$setQuery .= ', @' . $value[0] . ' = "' . $fields_values[$value[0]] . '"';
			} else {
				$setQuery .= ', @' . $value[0] . ' = ' . intval($fields_values[$value[0]]);
			}
			$addQueryFields .= ', @' . $value[0];
		}
		$this->registry->set('tx_kesearch', 'addQueryFields', $addQueryFields);
		
		// check if record already exists
		$countRows = $this->indexRecordExists($fields_values['orig_uid'], $fields_values['pid'], $fields_values['type']);
		if($countRows) { // update existing record
			$where = 'uid=' . intval($this->currentRow['uid']);
			unset($fields_values['crdate']);
			if ($debugOnly) { // do not process - just debug query
				t3lib_utility_Debug::debug($GLOBALS['TYPO3_DB']->UPDATEquery($table,$where,$fields_values),1);
			} else { // process storing of index record and return uid
				$diff = array_diff($this->currentRow, $fields_values);
				unset($diff['uid']);
				unset($diff['tstamp']);
				unset($diff['crdate']);
				if(count($diff)) { // If there any fields left, do a complete UPDATE
					$query = 'SET
						@pid = ' . $fields_values['pid'] . ',
						@title = "' . $fields_values['title'] . '",
						@type = "' . $fields_values['type'] . '",
						@targetpid = "' . $fields_values['targetpid'] . '",
						@content = "' . $fields_values['content'] . '",
						@tags = "' . $fields_values['tags'] . '",
						@params = "' . $fields_values['params'] . '",
						@abstract = "' . $fields_values['abstract'] . '",
						@language = ' . $fields_values['language'] . ',
						@starttime = ' . $fields_values['starttime'] . ',
						@endtime = ' . $fields_values['endtime'] . ',
						@fe_group = "' . $fields_values['fe_group'] . '",
						@tstamp = ' . $fields_values['tstamp'] . $setQuery . ',
						@uid = ' . $this->currentRow['uid'] . '
					';
					$GLOBALS['TYPO3_DB']->sql_query($query);
					//t3lib_div::devLog('dbSetUpdateComplete', 'dbSetUpdateComplete', -1, array($query, $GLOBALS['TYPO3_DB']->sql_error()));

					$query = '
						EXECUTE updateStmt USING @pid, @title, @type, @targetpid, @content, @tags, @params, @abstract, @language, @starttime, @endtime, @fe_group, @tstamp' . $addQueryFields . ', @uid;
					';
					$GLOBALS['TYPO3_DB']->sql_query($query);
					t3lib_div::devLog('dbUpdateComplete', 'dbUpdateComplete', -1, array($query, $GLOBALS['TYPO3_DB']->sql_error()));
				} else { // If there are no more fields in $diff, then UPDATE only tstamp
					$query = 'SET
						@tstamp = ' . $fields_values['tstamp'] . $setQuery . ',
						@uid = ' . $this->currentRow['uid'] . '
					';
					$GLOBALS['TYPO3_DB']->sql_query($query);
					//t3lib_div::devLog('dbSetUpdateTstamp', 'dbSetUpdateTstamp', -1, array($query, $GLOBALS['TYPO3_DB']->sql_error()));

					$query = '
						EXECUTE updateShortStmt USING @tstamp, @uid;
					';
					$GLOBALS['TYPO3_DB']->sql_query($query);
					t3lib_div::devLog('dbUpdateTstamp', 'dbUpdateTstamp', -1, array($query, $GLOBALS['TYPO3_DB']->sql_error()));
				}

				// count record for periodic notification?
				if ($this->extConf['periodicNotification']) $this->periodicNotificationCount();
				return true;
			}
		} else {
			// insert new record
			if ($debugOnly) { // do not process - just debug query
				t3lib_utility_Debug::debug($GLOBALS['TYPO3_DB']->INSERTquery($table, $fields_values, $no_quote_fields = FALSE), 1);
			} else { // process storing of index record and return uid
				$query = 'SET
					@pid = ' . $fields_values['pid'] . ',
					@title = "' . $fields_values['title'] . '",
					@type = "' . $fields_values['type'] . '",
					@targetpid = "' . $fields_values['targetpid'] . '",
					@content = "' . $fields_values['content'] . '",
					@tags = "' . $fields_values['tags'] . '",
					@params = "' . $fields_values['params'] . '",
					@abstract = "' . $fields_values['abstract'] . '",
					@language = ' . $fields_values['language'] . ',
					@starttime = ' . $fields_values['starttime'] . ',
					@endtime = ' . $fields_values['endtime'] . ',
					@fe_group = "' . $fields_values['fe_group'] . '",
					@tstamp = ' . $fields_values['tstamp'] . ',
					@crdate = ' . $fields_values['crdate'] . $setQuery . '
				';
				$GLOBALS['TYPO3_DB']->sql_query($query);
				//t3lib_div::devLog('db', 'db', -1, array($query, $GLOBALS['TYPO3_DB']->sql_error()));

				$query = '
					EXECUTE insertStmt USING @pid, @title, @type, @targetpid, @content, @tags, @params, @abstract, @language, @starttime, @endtime, @fe_group, @tstamp, @crdate' . $addQueryFields . ';
				';
				$GLOBALS['TYPO3_DB']->sql_query($query);
				//t3lib_div::devLog('db', 'db', -1, array($query, $GLOBALS['TYPO3_DB']->sql_error()));

				// count record for periodic notification?
				if ($this->extConf['periodicNotification']) $this->periodicNotificationCount();

				return $GLOBALS['TYPO3_DB']->sql_insert_id();
			}
		}
	}


	/**
	 * try to find an allready indexed record
	 * THX to PREPARE-Statements. They speed up indexing up to 50%
	 * This function also sets $this->currentRow
	 *
	 * @return amount of search results
	 */
	function indexRecordExists($uid, $pid, $type) {
		$GLOBALS['TYPO3_DB']->sql_query('SET
			@orig_uid = ' . $uid . ',
			@pid = ' . $pid . ',
			@type = "' . $type . '"
		');
		$res = $GLOBALS['TYPO3_DB']->sql_query('
			EXECUTE searchStmt USING @orig_uid, @pid, @type;
		');
		$this->currentRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		return $GLOBALS['TYPO3_DB']->sql_num_rows($res);
	}


	/**
	 * this function returns all indexer configurations found in DB
	 * independant of PID
	 */
	function getConfigurations() {
		$fields = '*';
		$table = 'tx_kesearch_indexerconfig';
		$where = '1=1 ';
		$where .= t3lib_befunc::BEenableFields($table);
		$where .= t3lib_befunc::deleteClause($table);
		return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($fields, $table, $where);
	}


	/*
	 * function periodicNotificationCount
	 * @return void
	 */
	function periodicNotificationCount() {
		// increase counter
		$this->counter += 1;

		// check if number of records configured reached
		if (($this->counter % $this->extConf['periodicNotification']) == 0) {
			// send the notification message
			if (t3lib_div::validEmail($this->extConf['notificationRecipient'])) {
				$msg = $this->counter.' records have been indexed.'."\n";
				$msg .= 'Indexer runs '.(time()-$this->startTime).' seconds \'til now.';
				mail($this->extConf['notificationRecipient'], $this->extConf['notificationSubject'], $msg);
			}
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_search/indexer/class.tx_kesearch_indexer.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/ke_search/indexer/class.tx_kesearch_indexer.php']);
}
?>
