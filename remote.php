<?php

use dokuwiki\Extension\Event;
use dokuwiki\Remote\ApiCall;
use dokuwiki\Extension\RemotePlugin;

class remote_plugin_remoteauth extends RemotePlugin
{
    public function getMethods()
    {
        $methods = parent::getMethods();
        foreach ($methods as $method) {
            $method->setPublic();
        }
        return $methods;
    }

    /**
     * stickyLogin
     *
     * @param string $user
     * @param string $pass
     * @return int
     */
    public function stickyLogin(string $user, string $pass)
    {
        global $conf;
        /** @var \dokuwiki\Extension\AuthPlugin $auth */
        global $auth;

        if (!$conf['useacl']) return 0;
        if (!$auth) return 0;

        @session_start(); // reopen session for login
        $ok = null;
        if ($auth->canDo('external')) {
            $ok = $auth->trustExternal($user, $pass, true);
        }
        if ($ok === null){
            $evdata = array(
                'user' => $user,
                'password' => $pass,
                'sticky' => true,
                'silent' => true,
            );
            $ok = Event::createAndTrigger('AUTH_LOGIN_CHECK', $evdata, 'auth_login_wrapper');
        }
        session_write_close(); // we're done with the session

        return $ok;
    }
}
