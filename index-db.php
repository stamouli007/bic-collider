<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Demos
 * @copyright  Copyright (c) 2005-2011 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * @see Zend_Feed
 */
require_once 'Zend/Feed.php';

/**
 * @see Zend_Search_Lucene
 */
require_once 'Zend/Search/Lucene.php';

//create the index
$index = new Zend_Search_Lucene('index', true);






/************************************************************
 * 
 *				Database Information
 *
 ************************************************************/
/*
 * Server for database
 */
$dbServer = 'internal-db.s121005.gridserver.com';

/*
 * Database User
 */
$dbUser = 'db121005';

/*
 * Database Password
 */
$dbPw = 'cr3ativ3';

/*
 * Database 
 */
$db = 'db121005_bic_hybrid';

/*
 * Database Table 
 */
//$dbTable = 'taxonomy_term_data';

/**
 * smlinux.com database connect
 */
$con = mysql_connect($dbServer,$dbUser,$dbPw) or mysql_error(); // server, user, pw


/**
 * Table Name with values
 */
@mysql_select_db($db, $con);

$result = mysql_query(
	"SELECT data.name AS name, data.tid AS dtid, data.weight AS weight, hierarchy.tid AS htid, hierarchy.parent AS parentId

	FROM taxonomy_term_data AS data, taxonomy_term_hierarchy AS hierarchy
	
	WHERE data.tid = hierarchy.tid
	
	"
);


if (!$result) {
    echo 'Could not run query: ' . mysql_error();
    exit;
}

if (mysql_num_rows($result) > 0) {
	while($row = mysql_fetch_assoc($result)) {
		//set_time_limit(100);
		echo "<pre>";
		//print_r($row);
		//echo $row['name'].' '.$row['dtid'].' '.$row['parentId'].' '.$row['weight'];
		
		$doc = new Zend_Search_Lucene_Document();
		
		if($row['parentId'] == 0) {
				$name = htmlentities(strip_tags($row['name']));
				$doc->addField(Zend_Search_Lucene_Field::Text('name', $name));
		
				//$id = htmlentities(strip_tags( $row['dtid'] ));
				//$doc->addField(Zend_Search_Lucene_Field::Text('id', $id));
		
				//$weight = htmlentities(strip_tags( $row['weight'] ));
				//$doc->addField(Zend_Search_Lucene_Field::Text('weight', $weight));
		
				echo "Adding {$name}...\n";
				$index->addDocument($doc);
		}else {
			
			    $parent = mysql_query(
					"SELECT data.name AS name, data.tid AS dtid, hierarchy.tid AS htid, hierarchy.parent AS parentId
					
					FROM taxonomy_term_data AS data, taxonomy_term_hierarchy AS hierarchy
					
					WHERE  hierarchy.parent = ".$row['parentId']."
					
					AND data.tid = hierarchy.parent
					
					LIMIT 1"
				);
				if (mysql_num_rows($parent) > 0) {
					$parentRow = mysql_fetch_assoc($parent);
				}
								
				$name = htmlentities(strip_tags($row['name']));
				$doc->addField(Zend_Search_Lucene_Field::Text('name', $name));
		
				//$id = htmlentities(strip_tags( $row['dtid'] ));
				//   $doc->addField(Zend_Search_Lucene_Field::Text('id', $id));
				
				//$parentId = htmlentities(strip_tags( $row['parentId'] ));
				//$doc->addField(Zend_Search_Lucene_Field::Text('parentid', $parentId));
				
				$parentName = htmlentities(strip_tags( $parentRow['name'] ));
				$doc->addField(Zend_Search_Lucene_Field::Text('parentName', $parentName));
		
				//$weight = htmlentities(strip_tags( $row['weight'] ));
				//$doc->addField(Zend_Search_Lucene_Field::Text('weight', $weight));
		
				echo "Adding {$name}...\n";
				$index->addDocument($doc);
		}	
		
	}
	$index->commit();
}

