<?php
/**
 * @version $Id$
 * @author Matthew McNaney <mcnaney at gmail dot com>
 */

class Checkin_Staff {
    var $id            = 0;
    var $user_id       = 0;
    var $filter        = null;
    var $filter_type   = 0;
    var $available     = 0;
    var $visitor_id    = 0;
    var $display_name = null;
    var $_reasons      = null;

    function Checkin_Staff($id=0)
    {
        if (empty($id)) {
            return true;
        }

        $this->id = (int)$id;
        if (!$this->init()) {
            $this->id = 0;
        }
    }

    function init()
    {
        $db = new PHPWS_DB('checkin_staff');
        $db->addJoin('left', 'checkin_staff', 'users', 'user_id', 'id');
        $db->addColumn('users.display_name');
        return $db->loadObject($this);
    }

    function loadReasons()
    {
        $db = new PHPWS_DB('checkin_reasons');
        $db->addColumn('id');
        $db->addColumn('summary');
        $db->setIndexBy('id');
        $result = $db->select('col');
        if (!PHPWS_Error::logIfError($result)) {
            $this->_reasons = & $result;
        }
    }

    function parseFilter($filter)
    {
        $this->filter = $filter;
    }

    function row_tags()
    {
        switch ($this->filter_type) {
        case 0 :
            $tpl['FILTER_INFO'] = dgettext('checkin', 'None');
            break;

        case CO_FT_LAST_NAME:
            $tpl['FILTER_INFO'] = sprintf(dgettext('checkin', 'Last name: %s'), $this->filter);
            break;

        case CO_FT_REASON:
            $this->loadReasons();
            $tpl['FILTER_INFO'] = implode('<br>', $this->_reasons);
            break;
        }
        $vars['staff_id'] = $this->id;
        $vars['aop'] = 'edit_staff';
        $links[] = PHPWS_Text::secureLink(dgettext('checkin', 'Edit'), 'checkin', $vars);
        $tpl['ACTION'] = implode(' | ', $links);
        return $tpl;
    }

    function save($new=false)
    {
        $db = new PHPWS_DB('checkin_staff');
        $result = !PHPWS_Error::logIfError($db->saveObject($this));
        // Save reason assignments

        return $result;
    }
}

?>