<?php

include 'MergeFiles.php';	// Script to merge sorted files

/**
 * Function that sorts input log file (according to 2 comparator indexes)
 * by reading one chunk of this file at a time, then applying merge sort 
 * algorithm on each chunk, and finally merge them into a sorted file
 * 
 * In our example, there are only 3 possible values for comparators:
 * 0: song_id
 * 1: usr_id
 * 2: country_code
 * 
 * However, this function has been designed as a generic function, 
 * and it accepts more than 3 log parameters per line
 * 
 * @param $filename, name of the log file that will be sorted
 * @param $sortedFilename, name of the sorted file that will be generated
 * @param $logParamPatterns, array containing the patterns of each log parameter (to detect data corruption)
 * @param $comparator1, the first comparator index (0: song_id, 1: usr_id, 2: country_code)
 * @param $comparator2, the second comparator index (0: song_id, 1: usr_id, 2: country_code)
 */
function sortLogFile($filename, $sortedFilename, $logParamPatterns, $comparator1, $comparator2) {
	global $red, $green, $blue, $noColor, $OK, $INDEXES;
	
	// Number of lines for each chunk of file (this length should depend on the length of the file to sort)
	$CHUNK_LENGTH = 10000;
	
	echo $blue."*** Sorting ".$filename." according to ".$INDEXES[$comparator1]." ***".$noColor."\n";
	
	$fh = fopen($filename, 'r') or die($red."Oops, couldn't open ".$filename."!".$noColor."\n\n");
	
	$nbTmpFiles = 0;	// Number of temporary files that will be created
	$nbLogParam = count($logParamPatterns);
	
	while(!feof($fh)) {
		$i = 0;
		$chunk = array();	// Chunk of data
		
		echo "Creating and sorting chunk file n°".$nbTmpFiles."... ";
		
		// Reading a chunk of desired length
		while($i < $CHUNK_LENGTH && !feof($fh)) {
			$line = trim(fgets($fh));
			$row = explode('|',$line);
			
			// Check if data is corrupted
			if(count($row) == $nbLogParam) {
				// Advanced detection of data corruption with pattern matching
				$isCorrupted = false;
				for($j=0; $j < $nbLogParam; $j++) {
					if(preg_match($logParamPatterns[$j], $row[$j]) != 1) {
						$isCorrupted = true;
						break;
					}
				}
				if(!$isCorrupted) {
					$chunk[] = $row;	// If data is not corrupted, it is added to the chunk
				} else {
					//echo "\n(Data corruption detected)\n";
				}
			}
			
			$i++;
		}
		
		// Then the chunk is sorted
		$chunk = mergeSort($chunk, $comparator1, $comparator2);
		
		// Store it into a chunk file
		$fp = fopen("chunk_".$nbTmpFiles.".log", 'w') or die($red."Oops, couldn't create a new file!".$noColor."\n\n");
		foreach($chunk as $chunkLine) {
			fwrite($fp, implode('|',$chunkLine)."\n");
		}
		
		fclose($fp);
		$nbTmpFiles++;
		unset($chunk);
		
		echo $OK;
	}
	fclose($fh);
	
	echo $green."Chunk files sorted!".$noColor."\n";
	
	$chunkNames = array();
	for($i=0; $i < $nbTmpFiles; $i++) {
		$chunkNames[] = "chunk_".$i.".log";
	}
	
	mergeFiles($sortedFilename, $chunkNames, $nbLogParam, $comparator1, $comparator2);

	echo $blue."*** DONE! ***".$noColor."\n";
}


/**
 * The famous merge sort algorithm implementation
 * 
 * $data is an array formatted as follow (with $i as an index):
 * list($song_id, $usr_id, $country_code) = $data[$i];
 * 
 * @param $data, a multidimensional array containing the log data
 * @param $comparator1, the first comparator index (0: song_id, 1: usr_id, 2: country_code)
 * @param $comparator2, the second comparator index (0: song_id, 1: usr_id, 2: country_code)
 */
function mergeSort($data, $comparator1, $comparator2) {
	if(count($data) <= 1) return $data;
	$mid = count($data) / 2;
	$left = array_slice($data, 0, $mid);
	$right = array_slice($data, $mid);
	$left = mergeSort($left, $comparator1, $comparator2);
	$right = mergeSort($right, $comparator1, $comparator2);
	return merge($left, $right, $comparator1, $comparator2);
}


/**
 * The merging function of the merge sort algorithm
 * The sorting mecanism is done according to the first comparator index, 
 * and in case of equality, the comparison is made with the second one
 * 
 * @param $left, the left part of $data array (from mergeSort function), recursively in the merge sort algorithm process
 * @param $right, the right part of $data array (from mergeSort function), recursively in the merge sort algorithm process
 * @param $comparator1, the first comparator index (0: song_id, 1: usr_id, 2: country_code)
 * @param $comparator2, the second comparator index (0: song_id, 1: usr_id, 2: country_code)
 */
function merge($left, $right, $comparator1, $comparator2) {
	$result = array();
	while(count($left) > 0 && count($right) > 0) {
		if($left[0][$comparator1] > $right[0][$comparator1] || 
		  ($left[0][$comparator1] == $right[0][$comparator1] && 
		   $left[0][$comparator2] > $right[0][$comparator2])) {
			$result[] = $right[0];
			$right = array_slice($right , 1);
		} else {
			$result[] = $left[0];
			$left = array_slice($left, 1);
		}
	}
	while (count($left) > 0) {
		$result[] = $left[0];
		$left = array_slice($left, 1);
	}
	while (count($right) > 0) {
		$result[] = $right[0];
		$right = array_slice($right, 1);
	}
	return $result;
}

?>
