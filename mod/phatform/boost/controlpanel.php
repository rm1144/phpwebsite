<?php

  /**
   * @version $Id: controlpanel.php 2 2006-04-27 20:24:18Z matt $
   * @author Matthew McNaney <mcnaney at gmail dot com>
   */

$link[] = array ('label'       => 'Form Generator',
                 'restricted'  => TRUE,
		 'module'      => 'phatform',
		 'url'         => 'index.php?module=phatform&amp;PHAT_MAN_OP=List',
		 'image'       => 'phatform.png',
		 'description' => 'Go here to create online forms that can be viewed and filled out by your site visitors.
                                   Reports can be made on the data submitted.',
		 'tab'         => 'content');

?>