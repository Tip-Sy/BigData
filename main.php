<?php

include 'SortingScript.php';	// Script to sort the log file
include 'TopFileGenerator.php';	// Script to generate the Top 50 file


ini_set('memory_limit', '1024M');				// Set the memory limit to 1Gb, as specified
date_default_timezone_set('Europe/Paris');		// Set default timezone to Paris


// Constants
$SONG_ID_INDEX = 0;		// Index of the 'song_id' log parameter
$USER_ID_INDEX = 1;		// Index of the 'user_id' log parameter
$COUNTRY_INDEX = 2;		// Index of the 'country_code' log parameter
$NB_LOG_PARAM = 3;		// Number of log parameters in each line of the log file
$TOP_NUMBER = 50;		// Number of elements in the Top (in our case: Top 50)
$INDEXES = array("song_id", "user_id", "country_code");		// Indexes names


// Patterns of each log parameter (in order to detect data corruption later)
$LOG_PARAM_PATTERNS = array('/[0-9]+/', '/[0-9]+/', '/[A-Z][A-Z]/');


// Colors and script messages shortcuts
$red = "\033[31m";
$green = "\033[0;32m";
$blue = "\033[0;34m";
$noColor = "\033[0m";
$OK = $green.'OK'.$noColor."\n";


/****************************************
 * STEP 0: Check for the file of the day
 ****************************************/
$filename = 'listenings-'.date('Ymd').'.log';
if(!file_exists($filename)) {
	exit($red."ERROR: Log file of the day not found, script aborted!".$noColor."\n");
}

/***************************************
 * STEP 1: Sort the log file of the day
 ***************************************/
// 1a) For User Top: sorting according to user_id and song_id
echo "\n".$blue."=== STEP 1 ===".$noColor."\n";
$userSortedFile = str_replace(".log", "_userSorted.log", $filename);
if(!file_exists($userSortedFile)) {
	sortLogFile($filename, $userSortedFile, $LOG_PARAM_PATTERNS, $USER_ID_INDEX, $SONG_ID_INDEX);
} else {
	echo $filename." is already sorted! ".$OK;
}
echo "\n***\n\n";

// 1b) For Country Top: sorting according to country_code and song_id
$countrySortedFile = str_replace(".log", "_countrySorted.log", $filename);
if(!file_exists($countrySortedFile)) {
	sortLogFile($filename, $countrySortedFile, $LOG_PARAM_PATTERNS, $COUNTRY_INDEX, $SONG_ID_INDEX);
} else {
	echo $filename." is already sorted! ".$OK;
}
echo "\n***\n\n";


/*****************************************************
 * STEP 2: Check the previous log files and sort them
 *****************************************************/
echo $blue."=== STEP 2 ===".$noColor."\n";
echo "Now checking the previous log files...\n\n";
$previousUserSortedFiles = array();
$previousCountrySortedFiles = array();
$previousUserSortedFiles[] = $userSortedFile;
$previousCountrySortedFiles[] = $countrySortedFile;
// Check the files of the 6 previous days
for($i=1; $i<7; $i++) {
	$timestamp = time() - 3600*24*$i;
	$previousLogFile = 'listenings-'.date('Ymd', $timestamp).'.log';
	$previousUserSortedFile = str_replace(".log", "_userSorted.log", $previousLogFile);
	$previousCountrySortedFile = str_replace(".log", "_countrySorted.log", $previousLogFile);
	
	// 2a) Check if the User Sorted file exists
	if(file_exists($previousUserSortedFile)) {
		$previousUserSortedFiles[] = $previousUserSortedFile;
	} else {
		// 2b) If it doesn't, look for the unsorted log file and sort it
		if(file_exists($previousLogFile)) {
			sortLogFile($previousLogFile, $previousUserSortedFile, $LOG_PARAM_PATTERNS, $USER_ID_INDEX, $SONG_ID_INDEX);
			$previousUserSortedFiles[] = $previousUserSortedFile;
			echo "\n***\n\n";
		}
	}
	
	// 2a) Idem for Country
	if(file_exists($previousCountrySortedFile)) {
		$previousCountrySortedFiles[] = $previousCountrySortedFile;
	} else {
		// 2b) Idem
		if(file_exists($previousLogFile)) {
			sortLogFile($previousLogFile, $previousCountrySortedFile, $LOG_PARAM_PATTERNS, $COUNTRY_INDEX, $SONG_ID_INDEX);
			$previousCountrySortedFiles[] = $previousCountrySortedFile;
			echo "\n***\n\n";
		}
	}
}
echo "***\n\n";


/**********************************
 * STEP 3: Merge all the log files
 **********************************/
echo $blue."=== STEP 3 ===".$noColor."\n";
$DELETE_FILES = false;	// Parameter of mergeFiles function: intermediate files shouldn't be deleted as they will be reused the next day
// 3a) Merge User files
if(count($previousUserSortedFiles) > 1) {
	$userWeeklySortedFile = str_replace(".log", "_userWeeklySorted.log", $filename);
	mergeFiles($userWeeklySortedFile, $previousUserSortedFiles, $NB_LOG_PARAM, $USER_ID_INDEX, $SONG_ID_INDEX, $DELETE_FILES);
} else {
	// If files are not merged, the Top file will be generated from the daily sorted file
	$userWeeklySortedFile = $userSortedFile;
}

// 3b) Merge Country files
if(count($previousCountrySortedFiles) > 1) {
	$countryWeeklySortedFile = str_replace(".log", "_countryWeeklySorted.log", $filename);
	mergeFiles($countryWeeklySortedFile, $previousCountrySortedFiles, $NB_LOG_PARAM, $COUNTRY_INDEX, $SONG_ID_INDEX, $DELETE_FILES);
} else {
	// If files are not merged, the Top file will be generated from the daily sorted file
	$countryWeeklySortedFile = $countrySortedFile;
}
echo "\n***\n\n";


/*****************************
 * STEP 4: Generate Top files
 *****************************/
echo $blue."=== STEP 4 ===".$noColor."\n";
// 4a) User Top
$userTopFilename = 'user_top'.$TOP_NUMBER.'_'.date('Ymd').'.txt';
generateTopFile($userWeeklySortedFile, $userTopFilename, $TOP_NUMBER, $NB_LOG_PARAM, $USER_ID_INDEX, $SONG_ID_INDEX);
echo "\n***\n\n";

// 4b) Country Top
$countryTopFilename = 'country_top'.$TOP_NUMBER.'_'.date('Ymd').'.txt';
generateTopFile($countryWeeklySortedFile, $countryTopFilename, $TOP_NUMBER, $NB_LOG_PARAM, $COUNTRY_INDEX, $SONG_ID_INDEX);
echo "\n";

?>
