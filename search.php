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
 * @see Zend_Search_Lucene
 */




// http://framework.zend.com/manual/1.11/en/learning.lucene.queries.html
//http://framework.zend.com/manual/1.11/en/zend.search.lucene.query-api.html
// ~ : fuzzy search 
// * : wildcard 
// ? : one letter wildcard


/************************************************************
 * 
 *				Starts the Search
 *
 ************************************************************/
init();

function init() {
	checkIfFieldsAreEmpty();
	
	$searchRaw = $_GET['s'];
	$searchClean = clean($searchRaw);
	
	$searchRawTwo = $_GET['otherField'];
	$searchCleanTwo = clean($searchRawTwo);
	$searchTwo = $searchCleanTwo['value'].'~';
	
	if ($searchClean['gibberish'] == 'true') {
		//Input is gibberish
		showGibberish();
	} else {
		//Cleans the string
		$search = $searchClean['value'].'~';
	}
	
	if(!(searchForTerm($search, $searchTwo, "true") == false)) {
		//Reults found and returns the json
		
	} else {		
		if ($searchCleanTwo['gibberish'] == 'true') {
			showGibberish();
		} 
		if(!(searchForTerm($searchTwo, $search, "true") == false)) {
			//Tries over field to see if there is results
		} else {
			//No results found
			noResult();
		}
	}
}



/************************************************************
 * 
 *				Search for Term
 *
 ************************************************************/
function searchForTerm($term, $termTwo, $log) {
	
	if($term == '~') {
		exit;
	}
	require_once 'Zend/Search/Lucene.php';
	
	// Setup first term Index
	$index = new Zend_Search_Lucene('index');
	/// First Term Search
	$hits   = $index->find(strtolower($term));
	$numberOfHits = count($hits);
	$termRaw = str_replace("~","",$term);
	// Checks if Black Listed
	for($i=0;$i <count($hits);$i++){
		if($hits[$i]->parentName == "53") {
			wordIsBlackListed();
		}
	}
	
	
	// Setup second Term Index
	$indexTwo = new Zend_Search_Lucene('index');
	$hitsTwo   = $index->find(strtolower($termTwo));
	// Checks if Black Listed
	for($i=0;$i <count($hitsTwo);$i++){
		if($hitsTwo[$i]->parentName == "53") {
			wordIsBlackListed();
		}
	}
	
	if($numberOfHits > 0) {
	
		$jsonResponse = array("video"=>array());
		
		$randomNumber = rand(0, $numberOfHits-1);
				
		$jsonRow = array(
						"wordFound"=>$hits[$randomNumber]->name,
						"parentName"=>$hits[$randomNumber]->parentName,
						);
			
		array_push($jsonResponse["video"], $jsonRow);
			
		
		if($log == "true"){
			//enterSearchTerm($termRaw, $hits[$randomNumber]->parentName);
		}
		
		// Found results
		$results = true;
		
		// Return the json
		echo str_replace('\/', '/',json_encode($jsonResponse));
		
	}else {
		// No Result found
		if($log == "true"){
			// Show Random Result
			noResult();
			//enterSearchTerm($termRaw, 0);
		}
		
		$jsonResponse = array("video"=>array());
		
		$jsonRow = array(
						"parentName"=>false,
						);
			
		array_push($jsonResponse["video"], $jsonRow);
		
		$results = false;
	}
	
	
	return $results;
}



/************************************************************
 * 
 *				Enters Search Term into Database
 *
 ************************************************************/
function enterSearchTerm($searchTerm, $parentName)
{
	$dbServer = 'localhost';
	$dbUser = 'root';
	$dbPw = 'gsxr1100';
	$db = 'bic_hybrid';
	$dbTable = 'search_terms';
	@$con = mysql_connect($dbServer,$dbUser,$dbPw) or mysql_error(); // server, user, pw
	/**
	 * Table Name with values
	 */
	@mysql_select_db($db, $con);

	
	$insertValues = "('".$searchTerm."', '".$parentName."')";
	$fieldNames = "(search, returned)";
	
	if(mysql_query("INSERT INTO $dbTable $fieldNames
		VALUES $insertValues")) {
			return true;
		}else {
			return false;
		}
	
}



/************************************************************
 * 
 *				Returns Descript of Video
 *
 ************************************************************/
function getDescription($parentID)
{
	$dbServer = 'localhost';
	$dbUser = 'root';
	$dbPw = 'gsxr1100';
	$db = 'bic_hybrid';
	$dbTable = 'taxonomy_term_data';
	@$con = mysql_connect($dbServer,$dbUser,$dbPw); // server, user, pw
	/**
	 * Table Name with values
	 */
	@mysql_select_db($db, $con);

	
	$query = mysql_query("SELECT data.description

	FROM $dbTable as data
	
	WHERE data.tid = $parentID");
	
	if (mysql_num_rows($query) > 0) {
		$result = mysql_fetch_row($query);
	}
	
	return $result[0];
	
	
}



/************************************************************
 * 
 *				Cleans Search
 *
 ************************************************************/
function clean($value)
{
	$gibberish = 'false';
	$string = array(
		'value' => '',
		'gibberish'=> $gibberish
		);
	$value = trim(stripslashes($value));
	
	$replaceOne = preg_replace('/([-,*,$,^,&,!,@,#,%])/','', $value); // symbols
	$replaceTwo = preg_replace('/\([^)]*\)|[()]/','', $value); // parenth
	$replaceThree = preg_replace('/[^(\x20-\x7F)]*/','', $value); // asci 
	$replaceFour = preg_replace('/(.)\1{2,}/','', $value); // 3 letters or more in a row
	
	//die($replaceOne . ' '.$replaceTwo.' '.$replaceThree);
	if(!($replaceOne == $value)) {
		//no replacement happened
		$gibberish = 'true';
	}
	if(!($replaceTwo == $value)) {
		$gibberish = 'true';
	}
	if(!($replaceThree == $value)) {
		$gibberish = 'true';
	}
	if(!($replaceFour == $value)) {
		$gibberish = 'true';
	}
	
	$string['value'] = $value;
	$string['gibberish'] = $gibberish;
	
	return $string;
}



/************************************************************
 * 
 *				Word is Gibberish
 *
 ************************************************************/
function showGibberish() {
	//die('The Word is gibberish');
	
	$randomVideoId = array(
		"xFhvZ9-OwKE", //3
		"LE4eZVm0Gk4" //17
	);
	
	$randomVidNumber = rand(0, count($randomVideoId)-1);
	$randomVideo = $randomVideoId[$randomVidNumber];
	
	$jsonResponse = array("video"=>array());
				
	$jsonRow = array(
					"wordFound"=>"random",
					"parentName"=>$randomVideo,
					);			
	array_push($jsonResponse["video"], $jsonRow);
	
	echo str_replace('\/', '/',json_encode($jsonResponse));
	
	exit();
}



/************************************************************
 * 
 *				No result at all
 *
 ************************************************************/
function noResult() {
	//die("<h1>No Result Has Been Found</h1>");
	
	$randomVideoId = array(
		"g-8dw6ea3LM", // 12
		"Ye5qCI3feTc", // 14
		"AUkzwA27OgY", // 16
		"cYAUAawpBhQ", // 26
		"oFA59uE3yKI", // 39
		"SzCxkr3PLjI", // 40
		"D5-65tfZ8N0", //41
		"6HSlh4Knilw"  //49
	);
	$randomVidNumber = rand(0, count($randomVideoId)-1);
	$randomVideo = $randomVideoId[$randomVidNumber];
	
	$jsonResponse = array("video"=>array());
				
	$jsonRow = array(
					"wordFound"=>"random",
					"parentName"=>$randomVideo,
					);			
	array_push($jsonResponse["video"], $jsonRow);
	
	echo str_replace('\/', '/',json_encode($jsonResponse));
	
	exit();
}


/************************************************************
 * 
 *		Checks to see if the fields are empty
 *
 ************************************************************/
function checkIfFieldsAreEmpty() {
	$valueOne = trim($_GET['s']);
	$valueTwo = trim($_GET['otherField']);
	
	if(empty($valueOne) || empty($valueTwo)) {
		$jsonResponse = array("video"=>array(
			"wordFound"=>"not found",
			"parentName"=>"404"
		));
	echo str_replace('\/', '/',json_encode($jsonResponse));
	exit();	
	}
}


/************************************************************
 * 
 *		The word returned as a Black Listed word
 *
 ************************************************************/
function wordIsBlackListed() {
	$jsonResponse = array("video"=>array(
			"wordFound"=>"forbidden",
			"parentName"=>"403"
	));
	echo str_replace('\/', '/',json_encode($jsonResponse));
	exit();	
}