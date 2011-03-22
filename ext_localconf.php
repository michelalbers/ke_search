<?php
if (!defined ('TYPO3_MODE')) {
 	die ('Access denied.');
}

// include filterlist class
include_once(t3lib_extMgm::extPath($_EXTKEY).'/classes/class.user_filterlist.php');

// include userTSConfig.txt
t3lib_extMgm::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:ke_search/pageTSconfig.txt">');

// change code field layout
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['list_type_Info']['ke_search_pi1']['ke_userregister']='EXT:ke_search/lib/class.tx_kesearch_cms_layout.php:tx_kesearch_cms_layout->getExtensionSummary';

// register cli-script
if (TYPO3_MODE=='BE')    {
    $TYPO3_CONF_VARS['SC_OPTIONS']['GLOBAL']['cliKeys'][$_EXTKEY] = array('EXT:'.$_EXTKEY.'/cli/class.cli_kesearch.php','_CLI_kesearch');
}

// add plugin
t3lib_extMgm::addPItoST43($_EXTKEY, 'pi1/class.tx_kesearch_pi1.php', '_pi1', 'list_type', 0);

// register hook for modifying pages index records
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyPagesIndexEntry'] = array('EXT:ke_search/hooks/class.user_kesearchhooks.php:user_kesearchhooks');
// register hook for modifying news index records
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyNewsIndexEntry'] = array('EXT:ke_search/hooks/class.user_kesearchhooks.php:user_kesearchhooks');
// register hook for modifying ke_yac index records
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyYACIndexEntry'] = array('EXT:ke_search/hooks/class.user_kesearchhooks.php:user_kesearchhooks');
// register hook for modifying dam index records
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyDAMIndexEntry'] = array('EXT:ke_search/hooks/class.user_kesearchhooks.php:user_kesearchhooks');
// register hook for modifying XTYPO Commerce index records
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['ke_search']['modifyXTYPOCommerceIndexEntry'] = array('EXT:ke_search/hooks/class.user_kesearchhooks.php:user_kesearchhooks');
?>
