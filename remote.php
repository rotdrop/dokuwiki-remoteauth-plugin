<?php 
class RemoteAuth extends DokuWiki_Remote_Plugin
{
    public function _getMethods()
    {
        return [
            'stickyLogin' => [
                'args' => [ 'string', 'string' ],
                'return' => 'int',
                'public' => 1,
                'doc' => 'Tries to perform a sticky login with the given credentials and sets auth cookies.',
            ]
        ];
    }
    
    /**
     * stringLogin
     *
     * @param string $user
     * @param string $pass
     * @return int
     */
    public function stickyLogin(string $user, string $pass) {
    {
        global $conf;
        /** @var \dokuwiki\Extension\AuthPlugin $auth */
        global $auth;

        if (!$conf['useacl']) return 0;
        if (!$auth) return 0;

        @session_start(); // reopen session for login
        $ok = null;
        if ($auth->canDo('external')) {
            $ok = $auth->trustExternal($user, $pass, false);
        }
        if ($ok === null){
            $evdata = array(
                'user' => $user,
                'password' => $pass,
                'sticky' => false,
                'silent' => true,
            );
            $ok = Event::createAndTrigger('AUTH_LOGIN_CHECK', $evdata, 'auth_login_wrapper');
        }
        session_write_close(); // we're done with the session

        return $ok;
    }
}

