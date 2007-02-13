<?php
  /**
   * The Current_User class is a shortcut to the Users class.
   * When using the Current_User you are acting on the user currently
   * logged into the system. Current_User is actually pathing through
   * the current user session.
   *
   * @author  Matthew McNaney <mcnaney at gmail dot com>
   * @version $Id$
   */

PHPWS_Core::initModClass('users', 'Users.php');

class Current_User {

    /**
     * Initializes the User session
     */
    function init($id)
    {
        $_SESSION['User'] = new PHPWS_User($id);
        $_SESSION['User']->setLogged(TRUE);
        Current_User::updateLastLogged();
        Current_User::getLogin();
    }

    function &getUserObj()
    {
        return $_SESSION['User'];
    }
  
    /**
     * Determines if a user is allowed to use a specific module, permission, and/or item
     *
     * @param  string   module             Name of the module checking
     * @param  string   subpermission      Name of the module permission to verify
     * @param  integer  item_id            Id of the item to verify
     * @param  string   itename            Name of the item permission
     * @param  boolean  unrestricted_only  If true, user must be have unrestricted 
     *                                     priviledges for that module regardless of 
     *                                     module, subpermission, or item id
     */
    function allow($module, $subpermission=NULL, $item_id=NULL, $itemname=NULL, $unrestricted_only=false)
    {
        if ($unrestricted_only && Current_User::isRestricted($module)) {
                return false;
        }
        return $_SESSION['User']->allow($module, $subpermission, $item_id, $itemname, FALSE);
    }

    /**
     * Works the same as the allow function but confirms the user's authorization code
     *
     * @param  string   module             Name of the module checking
     * @param  string   subpermission      Name of the module permission to verify
     * @param  integer  item_id            Id of the item to verify
     * @param  string   itename            Name of the item permission
     * @param  boolean  unrestricted_only  If true, user must be have unrestricted 
     *                                     priviledges for that module regardless of 
     *                                     module, subpermission, or item id
     */
    function authorized($module, $subpermission=NULL, $item_id=NULL, $itemname=NULL, $unrestricted_only=false)
    {
        if ($unrestricted_only && Current_User::isRestricted($module)) {
                return false;
        }

        return $_SESSION['User']->allow($module, $subpermission, $item_id, $itemname, TRUE);
    }

    function allowedItem($module, $item_id, $itemname=NULL)
    {
        return $_SESSION['User']->allowedItem($module, $item_id, $itemname);
    }

    /**
     * Verifies the user is a deity and their authorization code is permitted
     */
    function deityAllow()
    {
        return $_SESSION['User']->deityAllow();
    }

    /**
     * sends a user to the 403 error page and logs a message (if specified)
     * to the security log
     */
    function disallow($message=NULL)
    {
        PHPWS_User::disallow($message);
    }

    function getLogin()
    {
        PHPWS_Core::initModClass('users', 'Form.php');
        $login = User_Form::logBox();
        if (!empty($login)) {
            Layout::set($login, 'users', 'login_box', FALSE);
        }
    }

    /**
     * returns true is currently logged user is a deity
     */
    function isDeity()
    {
        return $_SESSION['User']->isDeity();
    }

    function getId()
    {
        return $_SESSION['User']->getId();
    }

    function getAuthKey()
    {
        if (!isset($_SESSION['User'])) {
            return NULL;
        }
        return $_SESSION['User']->getAuthKey();
    }

    function verifyAuthKey()
    {
        return $_SESSION['User']->verifyAuthKey();
    }

    function getUnrestrictedLevels()
    {
        return $_SESSION['User']->getUnrestrictedLevels();
    }

    /**
     * Returns true if the user is restricted. Note that false will be
     * returned on unrestricted users AND users who do not have module
     * permission. User permission must be checked separately.
     * You may want to use !isUnrestricted instead.
     */
    function isRestricted($module)
    {
        if (Current_User::isDeity()) {
            return FALSE;
        }
     
        $level = $_SESSION['User']->getPermissionLevel($module);
        return $level == RESTRICTED_PERMISSION ? TRUE : FALSE;
    }

    /**
     * @param integer id
     * @return True, if current user's id equals the parameter
     */
    function isUser($id)
    {
        return ($_SESSION['User']->id == $id) ? TRUE : FALSE;
    }

    /**
     * Returns true is the user has unrestricted access to a module.
     * Unlike isRestricted, user must be logged in and have module access
     */
    function isUnrestricted($module)
    {
        if (Current_User::isDeity()) {
            return TRUE;
        }

        if (empty($module)) {
            return FALSE;
        }

        if (!Current_User::allow($module)) {
            return FALSE;
        }

        $level = $_SESSION['User']->getPermissionLevel($module);
        return $level == UNRESTRICTED_PERMISSION ? TRUE : FALSE;
    }

    function updateLastLogged()
    {
        $db = new PHPWS_DB('users');
        $db->addWhere('id', $_SESSION['User']->getId());
        $db->addValue('last_logged', mktime());
        return $db->update();
    }

    function getUsername()
    {
        return $_SESSION['User']->getUsername();
    }

    function getDisplayName()
    {
        return $_SESSION['User']->getDisplayName();
    }

    function getEmail($html=FALSE,$showAddress=FALSE)
    {
        return $_SESSION['User']->getEmail($html,$showAddress);
    }

    function isLogged()
    {
        if (!isset($_SESSION['User'])) {
            $_SESSION['User'] = new PHPWS_User;
        }

        return $_SESSION['User']->isLogged();
    }

    function save()
    {
        return $_SESSION['User']->save();
    }

    function getPermissionLevel($module)
    {
        if ($_SESSION['User']->isDeity())
            return UNRESTRICTED_PERMISSION;

        return $_SESSION['User']->_permission->getPermissionLevel($module);
    }

    function giveItemPermission($module, $item_id, $itemname)
    {
        return Users_Permission::giveItemPermission(Current_User::getId(), $module, $item_id, $itemname);
    }

    function getCreatedDate()
    {
        return $_SESSION['User']->created;
    }

    function getIP()
    {
        return $_SERVER['REMOTE_ADDR'];
    }

    function getGroups()
    {
        if (empty($_SESSION['User']->_groups)) {
            return NULL;
        }
        return $_SESSION['User']->_groups;
    }

    function permissionMenu()
    {
        $key = Key::getCurrent();

        if (empty($key) || $key->isDummy() || empty($key->edit_permission)) {
            return;
        }

        if (Current_User::isUnrestricted($key->module) && 
            Current_User::allow($key->module, $key->edit_permission)) {

            if (!javascriptEnabled()) {
                $tpl = User_Form::permissionMenu($key);
                $content = PHPWS_Template::process($tpl, 'users', 'forms/permission_menu.tpl');
                Layout::add($content, 'users', 'permissions');
            } else {
                translate('users');
                $links[] = Current_User::popupPermission($key->id, sprintf(_('Set permissions'), $key->title));
                translate();
                MiniAdmin::add('users', $links);
            }
        }
    }

    function popupPermission($key_id, $label=NULL)
    {
        if (empty($label)) {
            translate('users');
            $js_vars['label'] = _('Permission');
            translate();
        } else {
            $js_vars['label'] = strip_tags($label);
        }

        $js_vars['width'] = 350;
        $js_vars['height'] = 325;

        $js_vars['address'] = sprintf('index.php?module=users&action=popup_permission&key_id=%s&authkey=%s',$key_id, Current_User::getAuthKey());

        return javascript('open_window', $js_vars);
    }

    /**
     * Returns true if the supplied username only contains characters defined
     * by the ALLOWED_USERNAME_CHARACTERS variable.
     */
    function allowUsername($username)
    {
        if (preg_match('/[^' . ALLOWED_USERNAME_CHARACTERS . ']/i', $username)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Logs in a user dependant on their authorization setting
     */
    function loginUser($username, $password)
    {
        if (!Current_User::allowUsername($username)) {
            return PHPWS_Error::get(USER_BAD_CHARACTERS, 'users', 'Current_User::loginUser');
        }

        $createUser = FALSE;
        // First check if they are currently a user
        $user = new PHPWS_User;
        $db = new PHPWS_DB('users');
        $db->addWhere('username', strtolower($username));
        $result = $db->loadObject($user);

        if (PEAR::isError($result)) {
            return $result;
        }

        // if result is blank then check against the default authorization
        if ($result == FALSE){
            $authorize = PHPWS_User::getUserSetting('default_authorization');
            $createUser = TRUE;
        } else {
            if (!$user->approved) {
                return PHPWS_Error::get(USER_NOT_APPROVED, 'users', 'Current_User::loginUser');
            }
            $authorize = $user->getAuthorize();
        }

        if (empty($authorize)) {
            return PHPWS_Error::get(USER_AUTH_MISSING, 'users', 'Current_User::loginUser');
        }

        $result = Current_User::authorize($authorize, $user, $password);

        if (PEAR::isError($result)){
            return $result;
        }

        if ($result == TRUE){
            if ($createUser == TRUE){
                $result = $user->setUsername($username);

                if (PEAR::isError($result)){
                    return $result;
                }

                $user->setAuthorize($authorize);
                $user->setActive(TRUE);
                $user->setApproved(TRUE);

                if (function_exists('post_authorize')) {
                    post_authorize($user);
                }

                $user->save();
            }

            $user->login();
            $_SESSION['User'] = $user;
            return TRUE;
        } else
            return FALSE;
    }

    function authorize($authorize, &$user, $password)
    {
        $db = new PHPWS_DB('users_auth_scripts');
        $db->setIndexBy('id');
        $result = $db->select();

        if (empty($result)) {
            return FALSE;
        }

        if (isset($result[$authorize])) { 
            extract($result[$authorize]);
            $file = PHPWS_SOURCE_DIR . 'mod/users/scripts/' . $filename;
            if(!is_file($file)){
                PHPWS_Error::log(USER_ERR_MISSING_AUTH, 'users', 'authorize', $file);
                return FALSE;
            }

            include $file;
            if (function_exists('authorize')){
                $result = authorize($user, $password);
                return $result;
            } else {
                PHPWS_Error::log(USER_ERR_MISSING_AUTH, 'users', 'authorize');
                return FALSE;
            }
        } else {
            return FALSE;
        }

        return $result;
    }


}

?>