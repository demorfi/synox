<?php

/**
 * Synology Download Station Hosting File.
 * For download torrent files to nnm-club.me.
 *
 * @author demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoFileHostingNNMClub
{

    /**
     * Url to download torrent.
     *
     * @var string
     * @access private
     */
    private $url;

    /**
     * Username for auth.
     *
     * @var string
     * @access private
     */
    private $username;

    /**
     * Password for auth.
     *
     * @var string
     * @access private
     */
    private $password;

    /**
     * Host information.
     *
     * @var array
     * @access private
     */
    private $hostInfo;

    /**
     * Auth tracker page.
     *
     * @var string
     * @access private
     */
    private $loginUrl = 'http://nnm-club.me/forum/login.php';

    /**
     * Cookie file.
     *
     * @var string
     * @access private
     */
    private $cookiePath = '/tmp/nnmclub.cookie';

    /**
     * Log file.
     *
     * @var string
     * @access private
     */
    private $logsPath = '/tmp/ht-nnmclub.log';

    /**
     * Debug mode.
     *
     * @var bool
     * @access private
     */
    private $debugMode = false;

    /**
     * Initialize.
     *
     * @param string $url Url to download torrent
     * @param string $username Username for auth
     * @param string $password Password for auth
     * @param array $hostInfo Host information
     * @access public
     */
    public function __construct($url, $username, $password, $hostInfo)
    {
        $this->debug('host init');

        if (strpos($username, '[opt:') !== false) {
            preg_match('/(\[opt:(d-(?P<debug>(\d)))?\])?/is', $username, $matches);
            $this->debugMode = isset($matches['debug']) && $matches['debug'] === '1';
            $this->debug('find options');

            // restore username
            $username = preg_replace('/(\[opt:\S+\])?(.*)/is', '$2', $username);
        }

        $this->url      = $url;
        $this->username = $username;
        $this->password = trim($password);
        $this->hostInfo = $hostInfo;
    }

    /**
     * @access public
     */
    public function __destruct()
    {
        $this->debug('host close');
    }

    /**
     * Send debug message to log.
     *
     * @param string $msg Debug message
     * @access protected
     * @return void
     */
    private function debug($msg)
    {
        $this->debugMode && file_put_contents($this->logsPath, $msg . PHP_EOL, FILE_APPEND);
    }

    /**
     * Auth account to tracker.
     *
     * @param string $username Username for auth
     * @param string $password Password for auth
     * @access private
     * @return bool
     */
    private function loginAccount($username, $password)
    {
        $curl = GenerateCurl(
            $this->loginUrl,
            array(
                CURL_OPTION_HEADER         => true,
                CURL_OPTION_FOLLOWLOCATION => true,
                CURL_OPTION_SAVECOOKIEFILE => $this->cookiePath,
                CURL_OPTION_POSTDATA       => array(
                    'username'  => $username,
                    'password'  => $password,
                    'autologin' => '1',
                    'login'     => ''
                )
            )
        );

        $this->debug('verify account ' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));
        $content = curl_exec($curl);
        curl_close($curl);

        return (strpos($content, 'privmsg.php?') !== false && file_exists($this->cookiePath));
    }

    /**
     * Check auth account to tracker.
     *
     * @param bool $clearCookie Clear use cookies
     * @access public
     * @return bool
     */
    public function Verify($clearCookie = false)
    {
        if ($clearCookie && file_exists($this->cookiePath)) {
            $this->debug('cookie clean');
            unlink($this->cookiePath);
        }

        if (!empty($this->username) && !empty($this->password)) {
            return ($this->loginAccount($this->username, $this->password) ? USER_IS_PREMIUM : LOGIN_FAIL);
        }

        $this->debug('verify account failure');
        return (LOGIN_FAIL);
    }

    /**
     * Get information download torrent.
     *
     * @access public
     * @return array
     */
    public function GetDownloadInfo()
    {
        if ($this->Verify() === USER_IS_PREMIUM) {
            $this->debug('verify account success');
            return (array(DOWNLOAD_URL => $this->url, DOWNLOAD_COOKIE => $this->cookiePath));
        }

        $this->debug('verify account failure');
        return (array(DOWNLOAD_ERROR => LOGIN_FAIL));
    }
}
