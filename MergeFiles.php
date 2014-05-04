<?php

include_once 'MyHeap.php';	// Heap class

/**
 * This function merges sorted files into one (big) sorted file,
 * according to 2 comparator indexes
 * 
 * @param $filename, name of the file that will be generated
 * @param $filesToMerge, array containing the names of the files to be merged
 * @param $nbLogParam, number of log parameters per line
 * @param $comparator1, the first comparator index (0: song_id, 1: usr_id, 2: country_code)
 * @param $comparator2, the second comparator index (0: song_id, 1: usr_id, 2: country_code)
 * @param $deleteFiles, boolean that indicates if the files must be deleted after merging them
 * @return $nbFiles, number of files that have been merged
 */
function mergeFiles($filename, $filesToMerge, $nbLogParam, $comparator1, $comparator2, $deleteFiles = true) {
	global $red, $green, $blue, $noColor, $OK;
	
	$nbFilesEnded = 0;	// Number of files that have reached the EOF
	
	echo "Now merging files... ";
	
	// Files are opened (if existing)
	$fh = array();
	$nbFiles = 0;
	foreach($filesToMerge as $file) {
		if(file_exists($file)) {
			$fh[] = fopen($file, 'r') or die($red."Oops, couldn't open ".$file."!".$noColor."\n\n");
			$nbFiles++;
		} else {
			echo $file." not found, it will not be merged...\n";
		}
	}
	
	if($nbFiles < 2) {
		echo "(No need to merge, operation canceled)\n";
		foreach($fh as $fhToClose) {
			fclose($fhToClose);
		}
		return $nbFiles;
	}
	
	// The sorted file is created
	$fp = fopen($filename, 'w') or die($red."Oops, couldn't create a new file!".$noColor."\n\n");
	
	// A heap is created to store the current line of each file, and to keep them sorted
	$heap = new MyHeap($comparator1, $comparator2);
	
	// Get the first value of each file, and add it to the heap (which remains sorted)
	for($i=0; $i<$nbFiles; $i++) {
		if(!feof($fh[$i])) {
			// At this point, data shall not be corrupted, nor empty
			$line = trim(fgets($fh[$i]));
			$row = explode('|',$line);
			
			// Trick: the index of the file associated to the data is also stored in the heap
			$row[] = $i;
			
			if(count($row) == ($nbLogParam + 1)) {
				$heap->insert($row);
			} else {
				// Corruption has already been detected before; if $row doesn't contain the whole data, something wrong must have happened meanwhile
				exit($red."Unexpected data corruption occurred... Script aborted!".$noColor."\n");
			}
			
		} else {
			// File shouldn't be empty at this point; if it is, something wrong must have happened meanwhile
			exit($red."Unexpected EOF reached... Script aborted!".$noColor."\n");
		}
	}
	
	// Extract the smallest data from the heap, and write it in the final file
	if(!$heap->isEmpty()) {
		$chunkLine = $heap->extract();					// Extraction of the data
		$indexOfLastFileRead = array_pop($chunkLine);	// Extraction of the index of the file from which data is merged
		fwrite($fp, implode('|',$chunkLine)."\n");		// Writing the data in the final file
	} else {
		// At this point, the heap shouldn't be empty; if it is, something wrong must have happened
		exit($red."Heap is unexpectedly empty... Script aborted!".$noColor."\n");
	}
	
	// Then the files are fully read and merged (until each of them reaches its EOF)
	while($nbFilesEnded < $nbFiles) {
		// The tricky part was to merge one line at a time, not the whole heap, and keep a trace of the last file read
		$i = $indexOfLastFileRead;
		
		// Reading the next line of the file from which data has just been merged
		if(!feof($fh[$i])) {
			$line = trim(fgets($fh[$i]));
			$row = explode('|',$line);
			$row[] = $i;
			if(count($row) == ($nbLogParam + 1)) {
				// If there is data, it is added to the heap
				$heap->insert($row);
			} else {
				// Data is expected to have ($nbLogParam + 1) values; if not, it means that the end of this file has been reached (case of last line empty)
				$nbFilesEnded++;
			}
		} else {
			$nbFilesEnded++;
		}
		
		// Writing the next sorted line into the final file
		if(!$heap->isEmpty()) {
			$chunkLine = $heap->extract();
			$indexOfLastFileRead = array_pop($chunkLine);
			fwrite($fp, implode('|',$chunkLine)."\n");
		}
	}
	
	// Closing all the files and deleting files if needed
	echo $OK."Cleaning up... ";
	foreach($fh as $fhToClose) {
		fclose($fhToClose);
	}
	if($deleteFiles) {
		foreach($filesToMerge as $file) {
			if(file_exists($file)) {
				unlink($file);
			}
		}
	}
	fclose($fp);
	unset($fh);
	unset($heap);
	
	echo $OK.$green."The ".$nbFiles." files have been merged successfully!".$noColor."\n";
	
	return $nbFiles;
}

?>