*****************************************
******** Technical Exam Solution ********
*****************************************

Hello,

This project contains my solution to a 
technical exam I had to take.

As I got a good feedback on my work, I 
decided to upload it in order to use it 
as an example of how I code.

To see the statement of the problem, 
please refer to the file "Statement.txt"

All the details about how I coded my
solution are in the comments of the 
code files.

Basically, here is how the script works:
0) Check for the log file of the day
1a) Sort it according to user_id
1b) Sort it according to country_code
2a) Check for the 6 previous log files
2b) Sort them if they aren't yet
3a) Merge sorted User log files
3b) Merge sorted Country log files
4a) Generate User Top
4b) Generate Country Top

===
* FILES:
1) main.php
   The main file, as its name suggests

2) SortingScript.php
   A function that sorts a log file by
   dividing it into multiple chunks, then
   using mergeSort algorithm to sort them
   
3) MergeFiles.php
   A function that merges sorted files
   into one file (also sorted)
   
4) TopFileGenerator.php
   A function that generates the Top
   file from a sorted log file
   
5) MyHeap.php:
   A Heap implementation that suits the
   problem
===
* REQUIREMENTS: 
- Linux environment
- 'php-cli' package installed
- 1Gb RAM
===
* HOW TO RUN IT:
Run 'php main.php'
===

Thanks for reading! :-)

*****************************************
**************** Tip-Sy *****************
*****************************************