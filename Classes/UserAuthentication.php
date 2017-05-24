<?php
namespace Pp\CodeSample;

use Bfc\Core\Object\SingletonInterface;

class UserAuthentication implements SingletonInterface
{
    /**
     * Session/Cookie name.
     *
     * @var string
     */
    protected $name = 'sample';

    /**
     * Session id.
     *
     * @var string
     */
    protected $id = '';

    /**
     * Session token.
     * Algorithm is hash sha256.
     *
     * @var string
     */
    protected $sessionToken = '';

    /**
     * Domain for the cookie.
     *
     * @var string
     */
    protected $domain = '';

    /**
     * The base path for the cookie.
     *
     * @var string
     */
    protected $path = '/index.php/my-sample/';

    /**
     * Secure for the cookie.
     * Use cookie only by https.
     *
     * @var bool
     */
    protected $secure = false;

    /**
     * Use cookie for authentication.
     *
     * @var bool
     */
    protected $useCookie = true;

    /**
     * Data stored in cookie.
     *
     * @var array
     */
    protected $cookieData = [];

    /**
     * Client session lifetime.
     * 0 = Session-cookie.
     * If session-cookies, the browser will stop the session when the browser is closed.
     * Otherwise this specifies the lifetime of a cookie that keeps the session.
     *
     * @var int Default is 3 day.
     */
    protected $lifetime = 60 * 60 * 24 * 3;

    /**
     * Session and cookie expire timestamp.
     *
     * @var int
     */
    protected $expire = 0;

    /**
     * Encryption string.
     * The string will be one-way encrypted for using as encryption key.
     *
     * @var string
     */
    private $encryptionString = 'PlatinPower.com GmbH 2017';

    /**
     * Encryption key.
     * The encoded from self::$encryptionString key is for using by en-/decrypt of data.
     *
     * @var string
     */
    private $encryptionKey = '';

    /**
     * Initialize object.
     * The magic method.
     */
    public function initializeObject()
    {
        $this->generateEncryptionKey();

        $this->domain = $_SERVER['HTTP_HOST'];
        $this->secure = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])
            ? true
            : false;
    }

    /**
     * Start session.
     */
    public function start()
    {
        $sessionId = session_id();
        if (!$sessionId) {
            // allow only one time start the session
            session_start();
            session_regenerate_id();
            $sessionId = session_id();
        }
        $this->id = $sessionId;

        if (!isset($_SESSION[$this->name])) {
            $_SESSION[$this->name] = [];
        }

        // set session expire
        if (!$this->isSetSessionExpire()) {
            $this->setSessionExpire();
        }

        $sessionExpire = $this->getSessionExpire();
        if ($sessionExpire > 0 && $sessionExpire < time()) {
            // by inactive time is expired
            $this->stop();
        } else {
            // by active prolong the time of expire
            $this->setSessionExpire();
        }

        if ($this->isUseCookie()) {
            $this->loadCookieData();

            // set expire of cookie
            $this->setExpire();
        }
    }

    /**
     * Stop session.
     */
    public function stop()
    {
        if (isset($_SESSION[$this->name])) {
            unset($_SESSION[$this->name]);
        }
    }

    /**
     * Login user.
     *
     * @param string $login The login identification
     * @param bool $stayLogin By true - set cookie for autologin, false - remove autologin cookie
     */
    public function login($login, $stayLogin = false)
    {
        $this->sessionToken = $this->createToken($login);
        $sessionToken = $this->sessionToken;

        if ($stayLogin !== true) {
            $this->setLifetime(0);
            $stayLogin = '0';
        } else {
            $stayLogin = '1';
        }

        if (!$this->isSetSession($sessionToken)) {
            $this->initSession($sessionToken);
            $this->setSession('login', base64_encode($login));
            $this->setSession('stayLogin', $stayLogin);
        }

        if ($this->isUseCookie()) {
            $this->setSessionCookie();
            $this->setCookieDataParam('uuid', $login);
            $this->setCookieDataParam('stayLogin', $stayLogin);
            $this->storeCookieData();
        } else {
            $this->removeCookie($this->name);
        }
    }

    /**
     * Logout user.
     */
    public function logout()
    {
        $this->setCookieDataParam('stayLogin', '0');
        $this->storeCookieData();

        $this->stop();
    }

    /**
     * Has cookie.
     *
     * @return bool
     */
    public function hasCookie()
    {
        if ($this->isUseCookie() && $this->isSetSessionCookie()) {
            return true;
        }

        return false;
    }

    /**
     * Get Login cookie.
     *
     * @return bool|string
     */
    public function getLoginCookie()
    {
        if (!$this->hasCookie()) {
            return false;
        }

        $login = '';
        $stayLogin = $this->getCookieDataParam('stayLogin');

        if ($stayLogin === '1') {
            $login = $this->getCookieDataParam('uuid');
        }

        return $login;
    }

    /**
     * Is user logged in session.
     *
     * @return bool
     */
    public function isLogged()
    {
        $isLogged = false;

        $sessionToken = $this->getSessionToken();

        if ($sessionToken) {
            $isLogged = $this->isSetSession($sessionToken);
        }

        return $isLogged;
    }

    /**
     * Get logged user login.
     *
     * @return bool|string
     */
    public function getLoggedUser()
    {
        $response = '';

        if ($this->isLogged()) {
            $response = $this->getSession('login');
            if ($response) {
                $response = base64_decode($response);
            }
        }

        return $response;
    }

    /**
     * Initialisation of session.
     *
     * @param $sessionToken
     */
    protected function initSession($sessionToken)
    {
        // init session structure
        if (!isset($_SESSION[$this->name])) {
            $_SESSION[$this->name] = [
                $sessionToken => [],
            ];
        }

        $_SESSION[$this->name]['token'] = $sessionToken;
    }

    /**
     * Get token of session.
     *
     * @return string
     */
    protected function getSessionToken()
    {
        return isset($_SESSION[$this->name]) && isset($_SESSION[$this->name]['token'])
            ? $_SESSION[$this->name]['token']
            : '';
    }

    /**
     * Get session value by key.
     *
     * @param string $key
     *
     * @return string
     */
    protected function getSession($key)
    {
        $value = '';

        $sessionToken = $this->getSessionToken();

        if (isset($_SESSION[$this->name][$sessionToken]) && isset($_SESSION[$this->name][$sessionToken][$key])) {
            $value = $_SESSION[$this->name][$sessionToken][$key];
        }

        return $value;
    }

    /**
     * Set value to session.
     *
     * @param string $key
     * @param mixed $value
     */
    protected function setSession($key, $value)
    {
        $sessionToken = $this->getSessionToken();
        if (!isset($_SESSION[$this->name][$sessionToken])) {
            $_SESSION[$this->name][$sessionToken] = [];
        }
        $_SESSION[$this->name][$sessionToken][$key] = $value;
    }

    /**
     * Is set session.
     *
     * @param string $sessionToken
     *
     * @return bool
     */
    protected function isSetSession($sessionToken)
    {
        return isset($_SESSION[$this->name]) && isset($_SESSION[$this->name][$sessionToken])
            ? true
            : false;
    }

    /**
     * Create token.
     *
     * @param string $str
     *
     * @return string
     */
    protected function createToken($str)
    {
        return hash('sha256', $str);
    }

    /**
     * Get the session cookie for the current disposal.
     *
     * @return string
     */
    protected function getSessionCookie()
    {
        $value = $this->getCookie('token');

        return $value;
    }

    /**
     * Sets the session cookie for the current disposal.
     *
     * @return void
     */
    protected function setSessionCookie()
    {
        $this->setCookie('token', $this->sessionToken);
    }

    /**
     * Checking whether the session cookie is set.
     *
     * @return bool
     */
    protected function isSetSessionCookie()
    {
        return isset($_COOKIE[$this->name])
            ? true
            : false;
    }

    /**
     * Get session expire.
     *
     * @return int
     */
    protected function getSessionExpire()
    {
        $sessionExpire = isset($_SESSION[$this->name]) && isset($_SESSION[$this->name]['expire'])
            ? $_SESSION[$this->name]['expire']
            : 0;

        return $sessionExpire;
    }

    /**
     * Set session expire.
     */
    protected function setSessionExpire()
    {
        $_SESSION[$this->name]['expire'] = time() + $this->lifetime;
    }

    /**
     * Checking whether the session expire is set.
     *
     * @return bool
     */
    protected function isSetSessionExpire()
    {
        return isset($_SESSION[$this->name]['expire'])
            ? true
            : false;
    }

    /**
     * Get the value of a cookie.
     *
     * @param string $key
     *
     * @return string
     */
    protected function getCookie($key)
    {
        if ($this->name && isset($_COOKIE[$this->name])) {
            $value = isset($_COOKIE[$this->name][$key])
                ? $_COOKIE[$this->name][$key]
                : '';
        } else {
            $value = isset($_COOKIE[$key])
                ? $_COOKIE[$key]
                : '';
        }

        $value = stripslashes($value);

        return $value;
    }

    /**
     * Set cookie.
     *
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    protected function setCookie($key, $value)
    {
        $cookieName = $this->getCookieName($key);

        $cookieDomain = $this->getDomain();
        $cookiePath = $this->getPath();
        $cookieExpire = $this->getExpire();
        // only for debugging
        //$cookieExpire = time() + (60 * 60 * 24);
        $cookieSecure = $this->isSecure();
        // Deliver cookies only via HTTP and prevent possible XSS by JavaScript
        $cookieHttpOnly = true;

        setcookie($cookieName, $value, $cookieExpire, $cookiePath, $cookieDomain, $cookieSecure, $cookieHttpOnly);
    }

    /**
     * Remove the cookie.
     *
     * @param string $cookieName
     *
     * @return void
     */
    public function removeCookie($cookieName)
    {
        $cookieDomain = $this->getDomain();
        // If no cookie domain is set, use the base path
        $cookiePath = $this->getPath();
        setcookie($cookieName, null, -1, $cookiePath, $cookieDomain);
    }

    /**
     * Creates a new session ID.
     *
     * @return string The new session ID
     */
    public function createSessionId()
    {
        $random = time() + mt_rand();
        $sessionId = hash('sha256', $random);

        return $sessionId;
    }

    /**
     * Store cookie data.
     * @note The cookie data are stored as encrypted.
     */
    protected function storeCookieData()
    {
        $token = $this->getSessionToken();
        if (!$token) {
            return;
        }

        $data = $this->getCookieData();
        $data = http_build_query($data);

        // all cookie data are encrypted stored on client side
        $data = $this->encryptData($data);
        $this->setCookie($token, $data);
    }

    /**
     * Load cookie data.
     *
     * @return array|bool
     */
    public function loadCookieData()
    {
        $token = $this->getCookieToken();

        if (!$token) {
            return false;
        }

        $data = $this->getCookie($token);
        // decrypt cookie data
        $data = $this->decryptData($data);

        // all parameters in data are in http query format: key1=value1&key2=value2
        if (strpos($data, '=') !== false) {
            $response = [];
            parse_str($data, $response);
            $this->setCookieData($response);
        } else {
            // data hasn't parameters or are in false format
            $response = false;
        }

        return $response;
    }

    /**
     * Get cookie token.
     *
     * @return string
     */
    protected function getCookieToken()
    {
        return $this->getCookie('token');
    }

    /**
     * Get full cookie name by key name.
     *
     * @param string $key
     *
     * @return string
     */
    protected function getCookieName($key)
    {
        $name = $this->getName();

        if (strlen($name) > 0) {
            $cookieName = $name . '[' . $key . ']';
        } else {
            $cookieName = $key;
        }

        return $cookieName;
    }

    /**
     * Get name of authentication key.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name of authentication key.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get id of session.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id of session.
     *
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Gets the domain to be used on setting cookies.
     *
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Gets the path to be used on setting cookies.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the path to be used on setting cookies.
     *
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * Get lifetime of session.
     *
     * @return int
     */
    public function getLifetime()
    {
        return $this->lifetime;
    }

    /**
     * Set lifetime if session.
     *
     * @param int $lifetime
     */
    public function setLifetime($lifetime)
    {
        $this->lifetime = $lifetime;
    }

    /**
     * Checking whether using of cookie is secure.
     *
     * @return bool
     */
    public function isSecure()
    {
        return $this->secure;
    }

    /**
     * Set secure using of cookie.
     *
     * @param bool $secure
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
    }

    /**
     * Checking whether the cookie is used.
     *
     * @return bool
     */
    public function isUseCookie()
    {
        return $this->useCookie;
    }

    /**
     * Set usage of cookie.
     *
     * @param bool $useCookie
     */
    public function setUseCookie($useCookie)
    {
        $this->useCookie = (bool) $useCookie;
    }

    /**
     * Check whether will be auto login by browser open.
     *
     * @return string
     */
    public function isStayLogin()
    {
        // first check cookie
        $stayLogin = $this->getCookieDataParam('stayLogin');

        if (empty($stayLogin)) {
            // if cookie not used, then check session
            $stayLogin = $this->getSession('stayLogin');
        }

        return $stayLogin;
    }

    /**
     * Get expire timestamp.
     *
     * @return int
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * Set expire timestamp.
     *
     * @param int $expire The default value -1 mean set expire as current time + lifetime.
     */
    public function setExpire($expire = -1)
    {
        if ($expire === -1) {
            // if expire is not defined, then use lifetime
            $lifetime = $this->getLifetime();
            $loginStay = $this->isStayLogin();

            if ($loginStay && $lifetime > 0) {
                $expire = time() + $lifetime;
            } else {
                $expire = 0;
            }
        }
        $this->expire = $expire;
    }

    /**
     * Get cookie data.
     *
     * @return array
     */
    public function getCookieData()
    {
        return $this->cookieData;
    }

    /**
     * Set cookie data.
     *
     * @param array $cookieData
     */
    public function setCookieData(array $cookieData)
    {
        $this->cookieData = $cookieData;
    }

    /**
     * Get parameter of cookie data.
     *
     * @param string $key
     *
     * @return string
     */
    public function getCookieDataParam($key)
    {
        return isset($this->cookieData[$key])
            ? $this->cookieData[$key]
            : '';
    }

    /**
     * Set parameter of cookie data.
     *
     * @param string $key
     * @param string $value
     */
    public function setCookieDataParam($key, $value)
    {
        $this->cookieData[$key] = $value;
    }

    /**
     * Add parameter to cookie data.
     *
     * @param string $key
     * @param string $value
     */
    public function addCookieDataParam($key, $value)
    {
        if (!isset($this->cookieData[$key])) {
            $this->cookieData[$key] = $value;
        }
    }

    /**
     * Remove parameter from cookie data.
     *
     * @param string $key
     */
    public function removeCookieDataParam($key)
    {
        if (isset($this->cookieData[$key])) {
            unset($this->cookieData[$key]);
        }
    }

    /**
     * Generate the encryption key.
     */
    private function generateEncryptionKey()
    {
        $this->encryptionKey = base64_encode(hash('sha256', $this->encryptionString));
    }

    /**
     * Get encryption key.
     *
     * @return string
     */
    private function getEncryptionKey()
    {
        return $this->encryptionKey;
    }

    /**
     * Encrypt data.
     *
     * @param string $data
     *
     * @return string
     */
    private function encryptData($data)
    {
        $encryptionKey = $this->getEncryptionKey();

        // Generate an initialization vector.
        $initializationVector = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        // Encrypt the data using AES 256 encryption in CBC mode using our encryption key and initialization vector.
        $data = openssl_encrypt($data, 'aes-256-cbc', $encryptionKey, 0, $initializationVector);
        // The $initializationVector is the important key for decrypting,
        // so save it with encrypted data using a unique separator (::).
        $data .= '::' . $initializationVector;

        $data = base64_encode($data);

        return $data;
    }

    /**
     * Decrypt data.
     *
     * @param string $data
     *
     * @return string
     */
    private function decryptData($data)
    {
        $encryptionKey = $this->getEncryptionKey();

        $data = base64_decode($data);

        // Split the encrypted data from initialization vector.
        list($data, $initializationVector) = explode('::', $data, 2);
        $data = openssl_decrypt($data, 'aes-256-cbc', $encryptionKey, 0, $initializationVector);

        return $data;
    }
}