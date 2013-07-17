<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "ke_search".
 *
 * Auto generated 17-07-2013 16:50
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Faceted Search',
	'description' => 'Faceted Search for TYPO3. Very easy to install. AJAX Frontend. Fast (tested with 50.000 records) and flexible (you can write your own indexers). Indexes content directly from the databases (no frontend crawling). Visit kesearch.de for further information.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '1.4.1',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Christian Buelter (kennziffer.com)',
	'author_email' => 'buelter@kennziffer.com',
	'author_company' => 'www.kennziffer.com GmbH',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
		    'php' => '5.3.0-0.0.0',
		    'typo3' => '4.5.0-6.1.99',
		),
		'conflicts' => '',
		'suggests' => array(
		),
	),
	'suggests' => array(
	),
	'_md5_values_when_last_written' => 'a:119:{s:9:"ChangeLog";s:4:"62be";s:16:"ext_autoload.php";s:4:"6cf9";s:21:"ext_conf_template.txt";s:4:"2c98";s:12:"ext_icon.gif";s:4:"1e65";s:17:"ext_localconf.php";s:4:"c4a8";s:14:"ext_tables.php";s:4:"a1d3";s:14:"ext_tables.sql";s:4:"1007";s:34:"icon_tx_kesearch_filteroptions.gif";s:4:"a25c";s:28:"icon_tx_kesearch_filters.gif";s:4:"6364";s:26:"icon_tx_kesearch_index.gif";s:4:"b932";s:34:"icon_tx_kesearch_indexerconfig.gif";s:4:"1938";s:25:"icon_tx_kesearch_tags.gif";s:4:"3fc0";s:17:"locallang_csh.xml";s:4:"0cf6";s:16:"locallang_db.xml";s:4:"12ae";s:16:"pageTSconfig.txt";s:4:"1fdb";s:10:"README.txt";s:4:"cb19";s:44:"selicon_tx_kesearch_indexerconfig_type_0.gif";s:4:"392f";s:44:"selicon_tx_kesearch_indexerconfig_type_1.gif";s:4:"8615";s:45:"selicon_tx_kesearch_indexerconfig_type_10.gif";s:4:"167d";s:45:"selicon_tx_kesearch_indexerconfig_type_11.gif";s:4:"7edf";s:44:"selicon_tx_kesearch_indexerconfig_type_2.gif";s:4:"3bcb";s:44:"selicon_tx_kesearch_indexerconfig_type_3.gif";s:4:"4dc0";s:44:"selicon_tx_kesearch_indexerconfig_type_5.gif";s:4:"2cb6";s:44:"selicon_tx_kesearch_indexerconfig_type_6.gif";s:4:"b727";s:44:"selicon_tx_kesearch_indexerconfig_type_7.gif";s:4:"b4ed";s:44:"selicon_tx_kesearch_indexerconfig_type_8.gif";s:4:"7a6b";s:44:"selicon_tx_kesearch_indexerconfig_type_9.gif";s:4:"cb59";s:7:"tca.php";s:4:"8ac5";s:47:"Tests/indexer/class.tx_kesearch_indexerTest.php";s:4:"f23e";s:52:"Tests/indexer/class.tx_kesearch_indexerTypesTest.php";s:4:"4b2c";s:46:"Tests/lib/class.tx_kesearch_dbOrderingTest.php";s:4:"2150";s:52:"Tests/lib/class.tx_kesearch_lib_searchphraseTest.php";s:4:"8562";s:52:"Tests/lib/class.tx_kesearch_lib_searchresultTest.php";s:4:"c6c8";s:47:"Tests/lib/class.tx_kesearch_libOrderingTest.php";s:4:"0f91";s:46:"classes/class.tx_kesearch_classes_flexform.php";s:4:"7783";s:33:"classes/class.user_filterlist.php";s:4:"c7c3";s:26:"cli/class.cli_kesearch.php";s:4:"e5fd";s:24:"cli/ke_search_indexer.sh";s:4:"5429";s:14:"doc/manual.sxw";s:4:"a9ed";s:19:"doc/wizard_form.dat";s:4:"babc";s:20:"doc/wizard_form.html";s:4:"ed3f";s:34:"hooks/class.user_kesearchhooks.php";s:4:"86e5";s:37:"indexer/class.tx_kesearch_indexer.php";s:4:"0721";s:43:"indexer/class.tx_kesearch_indexer_types.php";s:4:"ed0c";s:61:"indexer/filetypes/class.tx_kesearch_indexer_filetypes_doc.php";s:4:"a316";s:61:"indexer/filetypes/class.tx_kesearch_indexer_filetypes_pdf.php";s:4:"c70e";s:61:"indexer/filetypes/class.tx_kesearch_indexer_filetypes_ppt.php";s:4:"824b";s:61:"indexer/filetypes/class.tx_kesearch_indexer_filetypes_xls.php";s:4:"b28a";s:61:"indexer/filetypes/interface.tx_kesearch_indexer_filetypes.php";s:4:"7237";s:58:"indexer/types/class.tx_kesearch_indexer_types_comments.php";s:4:"2bf3";s:53:"indexer/types/class.tx_kesearch_indexer_types_dam.php";s:4:"8d8d";s:54:"indexer/types/class.tx_kesearch_indexer_types_file.php";s:4:"09fd";s:56:"indexer/types/class.tx_kesearch_indexer_types_ke_yac.php";s:4:"a311";s:57:"indexer/types/class.tx_kesearch_indexer_types_mmforum.php";s:4:"175e";s:54:"indexer/types/class.tx_kesearch_indexer_types_page.php";s:4:"bf46";s:61:"indexer/types/class.tx_kesearch_indexer_types_t3s_content.php";s:4:"e74e";s:61:"indexer/types/class.tx_kesearch_indexer_types_templavoila.php";s:4:"1d94";s:60:"indexer/types/class.tx_kesearch_indexer_types_tt_address.php";s:4:"a965";s:60:"indexer/types/class.tx_kesearch_indexer_types_tt_content.php";s:4:"e142";s:56:"indexer/types/class.tx_kesearch_indexer_types_ttnews.php";s:4:"603b";s:32:"lib/class.tx_kesearch_config.php";s:4:"81e0";s:28:"lib/class.tx_kesearch_db.php";s:4:"2c20";s:33:"lib/class.tx_kesearch_filters.php";s:4:"b883";s:29:"lib/class.tx_kesearch_lib.php";s:4:"4c46";s:33:"lib/class.tx_kesearch_lib_div.php";s:4:"d9c3";s:38:"lib/class.tx_kesearch_lib_fileinfo.php";s:4:"7910";s:35:"lib/class.tx_kesearch_lib_items.php";s:4:"6be4";s:42:"lib/class.tx_kesearch_lib_searchphrase.php";s:4:"a3ef";s:42:"lib/class.tx_kesearch_lib_searchresult.php";s:4:"359b";s:37:"lib/class.tx_kesearch_lib_sorting.php";s:4:"e9a1";s:55:"lib/filters/class.tx_kesearch_lib_filters_textlinks.php";s:4:"9974";s:13:"mod1/conf.php";s:4:"10db";s:14:"mod1/index.php";s:4:"3620";s:18:"mod1/locallang.xml";s:4:"daf0";s:22:"mod1/locallang_mod.xml";s:4:"b72e";s:19:"mod1/moduleicon.gif";s:4:"1e65";s:14:"pi1/ce_wiz.gif";s:4:"309c";s:29:"pi1/class.tx_kesearch_pi1.php";s:4:"7f5a";s:37:"pi1/class.tx_kesearch_pi1_wizicon.php";s:4:"a572";s:20:"pi1/flexform_pi1.xml";s:4:"517a";s:17:"pi1/locallang.xml";s:4:"90f8";s:14:"pi2/ce_wiz.gif";s:4:"309c";s:29:"pi2/class.tx_kesearch_pi2.php";s:4:"1e40";s:37:"pi2/class.tx_kesearch_pi2_wizicon.php";s:4:"3494";s:20:"pi2/flexform_pi2.xml";s:4:"a3db";s:17:"pi2/locallang.xml";s:4:"5143";s:14:"pi3/ce_wiz.gif";s:4:"309c";s:29:"pi3/class.tx_kesearch_pi3.php";s:4:"cf24";s:37:"pi3/class.tx_kesearch_pi3_wizicon.php";s:4:"9d0f";s:20:"pi3/flexform_pi3.xml";s:4:"a3db";s:17:"pi3/locallang.xml";s:4:"3674";s:21:"res/ke_search_pi1.css";s:4:"8dfc";s:20:"res/template_pi1.tpl";s:4:"5a79";s:23:"res/img/ajax-loader.gif";s:4:"0d08";s:22:"res/img/arrow-next.gif";s:4:"de58";s:22:"res/img/arrow-prev.gif";s:4:"9806";s:21:"res/img/attention.gif";s:4:"a8ba";s:17:"res/img/blank.gif";s:4:"3254";s:27:"res/img/default-indexer.gif";s:4:"0b8d";s:24:"res/img/filterHeadBG.gif";s:4:"54b5";s:27:"res/img/kesearch_submit.png";s:4:"9ac7";s:27:"res/img/list-bullet-cat.gif";s:4:"78ba";s:27:"res/img/list-head-bgrnd.gif";s:4:"54b5";s:28:"res/img/list-head-closed.gif";s:4:"281e";s:30:"res/img/list-head-expanded.gif";s:4:"8f38";s:27:"res/img/searchbox-bgrnd.gif";s:4:"66cf";s:19:"res/img/spinner.gif";s:4:"2029";s:26:"res/img/types/comments.gif";s:4:"7edf";s:25:"res/img/types/content.gif";s:4:"a659";s:21:"res/img/types/dam.gif";s:4:"4c43";s:24:"res/img/types/ke_yac.gif";s:4:"d502";s:25:"res/img/types/mmforum.gif";s:4:"167d";s:22:"res/img/types/page.gif";s:4:"a659";s:29:"res/img/types/templavoila.gif";s:4:"a659";s:28:"res/img/types/tt_address.gif";s:4:"ff1f";s:25:"res/img/types/tt_news.gif";s:4:"9e5f";s:25:"res/scripts/RemoveXSS.php";s:4:"17c6";s:19:"res/scripts/SVG.php";s:4:"3b1f";s:39:"tasks/class.tx_kesearch_indexertask.php";s:4:"be70";}',
);

?>