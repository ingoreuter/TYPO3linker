#Readme
##What does this script do?
This script creates all needed symbolic links for a TYPO3 setup, if you have the source folder outside your web root.

**Only TYPO3 6.X and 7.X are supported (it is not tested with TYPO3 8.X). Do not use this script for TYPO3 4.X** 

## How to uese this script

### Prepare the TYPO3 source
- Download TYPO3 from www.typo3.org/download
- Upload the zip-file to your server
- Unpack the zip-file to the destination, where you want to have the TYPO3 sources
	- The source folder is most likely outside your web root.
	- Now you should have a folder like "typo3_src-6.x.x". The folder contains (at least) the files "\_.htaccess",  "index.php" and a folder "typo3".

### Use this script
In order to have the TYPO3 source accessible in your web root, upload this script to your web root. If you have not renamed this script, it should be accessible through www.yourDoamin.tdl/linker.php

**In order to make sure, that the script can create symbolic links to the TYPO3, you have to ensure, that the script has the permission to write files on your server.**

Follow these steps to create the needed symbolic links (it is quite simple).

- Upload the script to your web root
- Run the script in your browser by www.yourDomain.tdl/linker.php
- Navigate to the TYPO3 source folder. (This is the folder form the first part. It should be named like "typo3_src-6.x.x".)
- After you have navigated to the source (you have to see the folders content in your browser!), click the button "Link this folder as TYPO3 source".
- If everything goes right, you are done!

After linking the source, you will be asked if you would like to copy the template for the .htacces file to your web root. If you agree, the "_.htaccess" file form the source folder will be copied to your web root.

**Notice:** If your web root already contains a folder named "typo3" or "typo3_src" the folder will **not** be deleted. The folder will be renamed to "typo3_old[longNumber]" or "tpo3_src_old[longNumber]". The same will happen to files called "\_.htaccess" or "index.php". If you have already symbolic links named "typo3", "typo3_src", "index.php" or "\_.htaccess" they will be overridden.

##Important##
**Delete this script from you server after you have created the links!** If you do not delete this script, everybody can browse your complete server and everybody can relink (this means destroy) your symbolic links for your TYPO3 setup.
