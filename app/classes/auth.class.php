<?php

class Auth
{

    public static function loggedInUser()
    {
        if (isset($_SESSION['framework']['user']['currentUser']['user_id'])) {
            return $_SESSION['framework']['user']['currentUser']['user_id'];
        } else {
            return false;
        }
    }

    public static function switchUser($userId)
    {
        $_SESSION['framework']['user']['currentUser']['user_id'] = $userId;
    }

    /**
     * This is out main method to call when processing a log in.
     * It runs authenticate and if that is successful calls loginFinish
     *
     * @param      $username
     * @param      $password
     * @param bool $remember
     * @return bool
     * @throws \Exception
     */
    public static function processLogin($username, $password, $remember = false)
    {
        if ($user = self::authenticate($username, $password)) {
            if (self::loginFinish($user, $remember)) {
                return true;
            }
        }

        self::logout();
        return false;
    }

    /**
     * This method checks whether a username and password are valid for a user
     *
     * @param $username
     * @param $password
     * @return bool|\PicORM\Model
     */
    public static function authenticate($username, $password)
    {
        $user = User::findOne(['email' => $username]);

        $authenticated = false;

        if (!empty($user->user_id)) {
            if (password_verify($password, $user->password)) {
                $authenticated = true;
            }
        }

        if (!$authenticated) {

            self::logout();
            sleep(1);

        } else {

            return $user;

        }

        return false;
    }

    /**
     * This method is to be called after successful authenticate. it sets up all the session and cookies
     *
     * @param      $user
     * @param bool $remember
     * @return bool
     * @throws \Exception
     */
    public static function loginFinish($user, $remember = false)
    {
        if (!isset($_SESSION['framework']['user']['cdeToken'])) {

            $tokenLeft = base64_encode(random_bytes(15));
			$tokenRight = base64_encode(random_bytes(33));
			$tokenRightHashed = hash('sha256', $tokenRight);

            // Insert into the database
            $tmpTokenExpiration = $remember ? time() + 31536000 : time() + 86400; // One Year if remember otherwise One Day

            $token = new UserToken();
            $token->user_id = $user->user_id;
			$token->user_login = $user->email;
			$token->selector = $tokenLeft;
			$token->hash = $tokenRightHashed;
			$token->expiration = gmdate("Y-m-d H:i:s", $tmpTokenExpiration);
			$token->remember = $remember ? 1 : 0;
			$token->last_validated = gmdate("Y-m-d H:i:s");
			$token->last_ip_address = $_SERVER['REMOTE_ADDR'];
			$token->last_session_id = session_id();
			$token->deleted = 0;

			$token->save();

            $tmpCookie = $token->token_id . '::' . $tokenLeft . '::' . $tokenRight;
            $_SESSION['framework']['user']['cdeToken'] = $tmpCookie;

            if ($remember) setcookie('cdeToken', $tmpCookie, $tmpTokenExpiration, BASE_PATH);

        }

        $_SESSION['framework']['user']['currentUser']['user_id'] = $user->user_id;
		$_SESSION['framework']['user']['currentUser']['email'] = $user->email;

		// this gets checked in the loggedIn() method to decide if we need to lookup our auth token again
		$_SESSION['framework']['user']['currentUser']['lastActivity'] = time();

		// update user last activity
		$user->last_activity = date('Y-m-d H:i:s');
		$user->save();

		return true;
    }

    /**
     * This is the core method to check if a user is logged in.
     * If last activity is greater than 60 seconds, we will look for a token in session variable then look it up in the DB
     *
     * @return bool
     * @throws \PicORM\Exception
     */
    public static function loggedIn()
    {
        $token = null;

        try {

            $userId = isset($_SESSION['framework']['user']['currentUser']['user_id']) ? $_SESSION['framework']['user']['currentUser']['user_id'] : 0;

            if (!$userId) {
                $cdeToken = $_COOKIE['cdeToken'];
                if (!empty($cdeToken)) $_SESSION['framework']['user']['cdeToken'] = $cdeToken;
            }

            if (isset($_SESSION['framework']['user']['cdeToken'])) {

                // we have an authentication token

                 $tmpLastTime = isset($_SESSION['framework']['user']['currentUser']['lastActivity']) ? $_SESSION['framework']['user']['currentUser']['lastActivity'] : 0;

                 if (time() > $tmpLastTime + 60) { // if last activity is greater than 60 seconds, we want to check for a token

                    $tokenExploded = explode("::",$_SESSION['framework']['user']['cdeToken']);
                    list($tokenId, $tokenLeft, $tokenRight) = $tokenExploded;

                    if (count($tokenExploded) == 3 && strlen($tokenLeft) == 20 && strlen($tokenRight) == 44) { // check length of token variables

                        // load the toke from the DB
                        $token = UserToken::findOne(['token_id' => $tokenId, 'selector' => $tokenLeft]);

                        $tokenRightHashed = hash('sha256', $tokenRight);

                        if (hash_equals($token->hash, $tokenRightHashed)) {

                            // token is valid. reload user info via loginFinish. This sets the current logged in user sessions
                            $user = User::findOne(['user_id' => $token->user_id]);
                            if (!empty($user->user_id)) {

                                self::loginFinish($user);

                                $token->last_validated = gmdate("Y-m-d H:i:s");
                                $token->last_ip_address = $_SERVER['REMOTE_ADDR'];
                                $token->last_session_id = session_id();
                                $token->save();

                                return true;

                            } throw new Exception('Invalid User ' . __LINE__);

                        } throw new Exception('Invalid Token ' . __LINE__);

                    } throw new Exception('Invalid Token ' . __LINE__);

                } else return true;

            }

            throw new Exception();

        } catch(Exception $e) {

            if (isset($token->token_id)) $token->delete();
            self::logout();
            return false;

        }
    }

    public  static function logout()
    {
		$_SESSION = [];
		self::deleteCookies();
    }

    public static function deleteCookies()
    {
        $tmpCookieLife = time() - 3600;
		setcookie('cdeToken', "", $tmpCookieLife, BASE_PATH);
	}

	public static function isAdmin($userId = 0)
    {
        if (empty($userId)) {
            $userId = self::loggedInUser();
        }

        if (!$userId) return false;

        /** @var \User $user */
        $user = User::findOne(['user_id' => $userId]);

        if ($user && $user->admin == 1) {
            return true;
        }

        return false;
    }

}
