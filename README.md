# Big Data sorting problem #

## General description ##

* This project contains my solution to the programming contest I sum up below
* The aim of the requested script is to process data from big log files, and generate sorted files
* **Input**: big log files containing formatted data of song listenings
* **Output**: two sorted files giving the 50 most listened songs according to various criteria

### Input files ###

* The names of the log files follow this pattern: **listenings-YYYYMMDD.log**
* Each log file contains all the listenings of the date *YYYYMMDD*
* Each line represents one listening, and is formatted as follows: ***song_id|user_id|country_code***
* **song_id**: ID of the song listened
* **user_id**: ID of the user who listened to the song
* **country_code**: ISO code of the country in which the user listened to the song

### Output file 1 ###

* Name: **countryTop50-YYYYMMDD.txt**
* Description: Top 50 of the most listened songs per country, for the last seven days
* Line format: ***country_code|sng_id1:n1,sng_id2:n2,...,sng_id50:n50***
* Note: **sng_id1** is the ID of the first song, and **n1** is equal to its number of listenings

### Output file 2 ###

* Name: **userTop50-YYYYMMDD.txt**
* Description: Top 50 of the most listened songs per user, for the last seven days
* Line format: ***user_id|sng_id1:n1,sng_id2:n2,...,sng_id50:n50***
* Note: **sng_id1** is the ID of the first song, and **n1** is equal to its number of listenings


## How does the script work? ##

### Running steps ###

* 0) Check for the log file of the current day
* 1a) Sort it according to user_id
* 1b) Sort it according to country_code
* 2a) Check for the 6 previous log files
* 2b) Sort them if they aren't yet
* 3a) Merge sorted User log files
* 3b) Merge sorted Country log files
* 4a) Generate User Top
* 4b) Generate Country Top

### Description of the files ###

* [main.php](main.php): The file that runs the script
* [SortingScript.php](SortingScript.php): A function that sorts an input file using merge sort algorithm
* [MergeFiles.php](MergeFiles.php): A function that merges multiple sorted files into one sorted file
* [TopFileGenerator.php](TopFileGenerator.php): A function that generates the Top file from a sorted log file
* [MyHeap.php](MyHeap.php): A Heap implementation that suits the problem
* Sample/[sample.log](Sample/sample.log): Sample log file for testing purposes

## How do I get set up? ##

1. Prerequisite: Linux environment with 'php-cli' package installed
2. Add some log files in current directory ([here is a sample](Sample/sample.log))
3. Rename log files using the **current date** and previous ones in: **listenings-YYYYMMDD.log**
5. Run the command: ***php main.php***


### Contribution guidelines ###

* If you have any suggestion, please contribute! :)

.

***Tip-Sy***
