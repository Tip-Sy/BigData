<?php

include_once 'MyHeap.php';	// Heap class

/**
 * This function generates a 'Top' file from a sorted log file
 * For each criterion (which index is $criterionIndex), 
 * it counts the occurrence of the values which index are $valueIndex
 * 
 * @param $filename, name of the sorted log file
 * @param $topFilename, name of the top file that will be generated
 * @param $topNumber, number of the Top such as 'Top ($topNumber)' is generated
 * @param $nbLogParam, number of log parameters per line
 * @param $criterionIndex, the index of the criterion for the top (0: song_id, 1: usr_id, 2: country_code)
 * @param $valueIndex, the index of the value that will be counted (0: song_id, 1: usr_id, 2: country_code)
 */
function generateTopFile($filename, $topFilename, $topNumber, $nbLogParam, $criterionIndex, $valueIndex) {
	global $red, $green, $blue, $noColor, $OK;
	
	echo $blue."*** Generating ".$topFilename." ***".$noColor."\n";
	
	// The sorted log file is opened
	$fh = fopen($filename, 'r') or die($red."Oops, couldn't open ".$filename."!".$noColor."\n\n");

	// The top file is created
	$fp = fopen($topFilename, 'w') or die($red."Oops, couldn't create a new file!".$noColor."\n\n");
	
	$currentCriterionID = 0;	// ID of the criterion that is currently analyzed
	$currentValueID = 0;		// ID of the value that is currently counted
	$counter = 0;				// Counter for the values
	
	// In order to keep the Top updated for each criterion, the data is stored in a MinHeap each time.
	// The structure of this array is as the following: list($valueID, $counter) = $array;
	// Thus, MyHeap class is used and sorted according to $counter (and then according to $valueID in case of equality)
	$VALUE_INDEX = 0;
	$COUNTER_INDEX = 1;
	$heap = new MyHeap($COUNTER_INDEX, $VALUE_INDEX);
	
	echo "Reading and counting data... ";
	
	// The first line is read
	if(!feof($fh)) {
		$line = trim(fgets($fh));
		$row = explode('|',$line);
		$currentCriterionID = $row[$criterionIndex];
		$currentValueID = $row[$valueIndex];
		$counter++;
	} else {
		// File shouldn't be empty at this point; if it is, something wrong must have happened meanwhile
		exit($red."Unexpected EOF reached... Script aborted!".$noColor."\n");
	}
	
	while(!feof($fh)) {
		$line = trim(fgets($fh));
		$row = explode('|',$line);
		
		if(count($row) == $nbLogParam) {
			// When next criterion is detected, the Top of the $currentCriterionID is written in the file
			if($currentCriterionID != $row[$criterionIndex]) {
				// First, insert the last data of $currentCriterionID into the heap
				$heap->insert(array($currentValueID, $counter));
				if($heap->count() > $topNumber) {
					$heap->extract();	// Maximum length of the heap is equal to $topNumber
				}
				
				// Empty MinHeap and reverse it (with a stack) to get the Top in descending order
				// Note: An array is used as a stack (instead of SplStack) for better performance
				$stack = array();
				while(!$heap->isEmpty()) {
					$stack[] = $heap->extract();
				}
				
				// Write the Top of the $currentCriterionID (in our example: Top 50)
				// Format: country|sng_id1:n1,sng_id2:n2,sng_id3:n3,...,sng_id50:n50
				$data = array_pop($stack);
				fwrite($fp, $currentCriterionID."|".$data[$VALUE_INDEX].":".$data[$COUNTER_INDEX]);
				while(!empty($stack)) {
					$data = array_pop($stack);
					fwrite($fp, ",".$data[$VALUE_INDEX].":".$data[$COUNTER_INDEX]);
				}
				fwrite($fp, "\n");
				unset($stack);
				
				// Update $currentCriterionID, $currentValueID and $counter
				$currentCriterionID = $row[$criterionIndex];
				$currentValueID = $row[$valueIndex];
				$counter = 1;
				
			} elseif($currentValueID != $row[$valueIndex]) {
				// When next value is detected, insert the last data of $currentCriterionID into the heap
				$heap->insert(array($currentValueID, $counter));
				if($heap->count() > $topNumber) {
					$heap->extract();	// 
				}
				
				// Then update $currentValueID and $counter
				$currentValueID = $row[$valueIndex];
				$counter = 1;
				
			} else {
				// Else, keep counting
				$counter++;
			}
		} else {
			// Data is expected to have ($nbLogParam) values;
			// if not, it means that the end of this file has been reached
			break;
		}
	}
	
	// Repeat the process for the last Top
	$heap->insert(array($currentValueID, $counter));
	if($heap->count() > $topNumber) {
		$heap->extract();
	}
	
	$stack = array();
	while(!$heap->isEmpty()) {
		$stack[] = $heap->extract();
	}
	
	$data = array_pop($stack);
	fwrite($fp, $currentCriterionID."|".$data[$VALUE_INDEX].":".$data[$COUNTER_INDEX]);
	while(!empty($stack)) {
		$data = array_pop($stack);
		fwrite($fp, ",".$data[$VALUE_INDEX].":".$data[$COUNTER_INDEX]);
	}
	
	echo $OK.$blue."*** DONE! ***".$noColor."\n";
	
	unset($stack);
	unset($heap);
	fclose($fh);
	fclose($fp);
}

?>
