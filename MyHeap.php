<?php

/**
 * This Heap class is an upgraded MinHeap that is suited for arrays
 * It keeps the data sorted according to 2 comparator indexes
 * Requirement: The inserted data must be arrays
 * 
 * Note: it is used in the merging process of the 'mergeFiles' function, 
 * and in the generation process of Top 50 file in the 'generateTopFile' function
 */
class MyHeap extends SplHeap {
	private $comparator1;
	private $comparator2;
	
	public function __construct($comparator1, $comparator2) {
		$this->comparator1 = $comparator1;
		$this->comparator2 = $comparator2;
	}
	
    public function compare($array1, $array2) {
		// First comparison according to $comparator1
        if($array1[$this->comparator1] == $array2[$this->comparator1]) {
			// In case of equality, a second comparison is made according to $comparator2
			if($array1[$this->comparator2] == $array2[$this->comparator2]) return 0;
			return $array1[$this->comparator2] < $array2[$this->comparator2] ? 1 : -1;
		}
        return $array1[$this->comparator1] < $array2[$this->comparator1] ? 1 : -1;
    }
}

?>