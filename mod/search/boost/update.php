<?php
  /**
   * update file for search
   *
   * @author Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

function search_update(&$content, $currentVersion)
{
    switch ($currentVersion) {
    case version_compare($currentVersion, '0.2.0', '<'):
        $files[] = 'conf/en_us_wordlist.txt';
        $files[] = 'conf/wordlist.txt';
        $result = PHPWS_Boost::updateFiles($files, 'search');
        if ($result) {
            if (PEAR::isError($result)) {
                PHPWS_Error::log($result);
                $content[] = 'Unable to copy wordlist files locally.';
            } else {
                $content[] = 'Wordlist files updated.';
            }
        }  else {
            $content[] = 'Wordlist files updated.';
        }
        $content[] = '<pre>
0.2.0 Changes
-------------
+ Moved deletion of keyword to its own function
+ Fixed javascript confirmation on item deletion
+ Deletion of keyword is now htmlentitified to catch foreign
  characters
+ Added resetKeywords function
+ FilterWords now has a htmlentities parameter and allows foreign
  characters (bug #1602039). Thanks WeBToR
+ Config file is no longer hardcoded. Now picks file based on language.
+ If all search words are undersized, search will not throw an
  error anymore.
</pre>';

    case version_compare($currentVersion, '0.2.1', '<'):
        $content[] = '<pre>
0.2.1 Changes
-------------
+ Added translate functions.
</pre>';        
    }

    return TRUE;
}


?>