<?php

if (!extension_loaded('dom')) {
    dl('dom.so');
}

if (!class_exists('phpQuery')) {
    include(__DIR__ . DIRECTORY_SEPARATOR . 'phpQuery.php');
}

if (!class_exists('SynoDLMSearchKinozalItem')) {
    include(__DIR__ . DIRECTORY_SEPARATOR . 'SynoDLMSearchKinozalItem.php');
}

/**
 * Synology Download Station Search File.
 * For search torrent files to kinozal.tv.
 *
 * @author demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoDLMSearchKinozal
{

    /**
     * Log file.
     *
     * @var string
     * @access private
     */
    private $logsPath = '/tmp/bt-kinozal.log';

    /**
     * Max loading page.
     *
     * @var int
     * @access private
     */
    private $numPages = 1;

    /**
     * Found links in page.
     *
     * @var array
     * @access private
     */
    private $linksPage = array();

    /**
     * Use single mode.
     *
     * @var bool
     * @access private
     */
    private $singleMode = false;

    /**
     * Curl resource for requests.
     *
     * @var resource
     * @access protected
     */
    protected $curl;

    /**
     * Query search.
     *
     * @var string
     * @access protected
     */
    protected $query = '';

    /**
     * Prefix url.
     *
     * @var string
     * @access protected
     */
    protected $pagePrefix = 'http://kinozal.tv';

    /**
     * Query send.
     *
     * @var string
     * @access protected
     */
    protected $queryUrl = 'http://kinozal.tv/browse.php?s=%s&g=0&page=%d';

    /**
     * Auth tracker page.
     *
     * @var string
     * @access protected
     */
    protected $loginUrl = 'http://kinozal.tv/takelogin.php';

    /**
     * Cookie file.
     *
     * @var string
     * @access protected
     */
    protected $cookiePath = '/tmp/kinozal.cookie';

    /**
     * Debug mode.
     *
     * @var bool
     * @access protected
     * @static
     */
    protected static $debugMode = false;

    /**
     * Initialize curl.
     *
     * @access public
     */
    public function __construct()
    {
        $this->debug('search init');

        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_HEADER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, DOWNLOAD_TIMEOUT);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, DOWNLOAD_TIMEOUT);
        curl_setopt($this->curl, CURLOPT_USERAGENT, DOWNLOAD_STATION_USER_AGENT);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($this->curl, CURLOPT_COOKIEJAR, $this->cookiePath);
        curl_setopt($this->curl, CURLOPT_COOKIEFILE, $this->cookiePath);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_REFERER, $this->pagePrefix);
    }

    /**
     * Close curl resource.
     *
     * @access public
     */
    public function __destruct()
    {
        is_resource($this->curl) && curl_close($this->curl);
        $this->debug('search close');
    }

    /**
     * Send debug message to log.
     *
     * @param string $msg Debug message
     * @access protected
     * @return void
     */
    protected function debug($msg)
    {
        self::$debugMode && file_put_contents($this->logsPath, $msg . PHP_EOL, FILE_APPEND);
    }

    /**
     * Get dom query.
     *
     * @param $content
     * @access private
     * @return phpQueryObject
     */
    private function getQuery($content)
    {
        return (phpQuery::newDocument($content));
    }

    /**
     * Send query to tracker.
     *
     * @param resource $curl Resource curl
     * @param string $query Search query
     * @param string $username Username for auth
     * @param string $password Password for auth
     * @access public
     * @return bool
     */
    public function prepare($curl, $query, $username = null, $password = null)
    {
        $this->debug('prepare method run');
        $password = trim($password);

        // check settings in username
        if (strpos($username, '[opt:') !== false) {
            preg_match('/(\[opt:(p-(?P<page>(\d+)))?(d-(?P<debug>(\d)))?\])?/is', $username, $matches);
            self::$debugMode = isset($matches['debug']) && $matches['debug'] === '1';
            $this->debug('find username options');

            // restore username
            $username = preg_replace('/(\[opt:\S+\])?(.*)/is', '$2', $username);
        }

        // check settings in query
        if (strpos($query, '[opt:') !== false) {
            preg_match('/(\[opt:(p-(?P<page>(\d+)))?(d-(?P<debug>(\d)))?(h-(?P<host>(\S+)))?\])?/is', $query, $matches);
            self::$debugMode = isset($matches['debug']) && $matches['debug'] === '1';
            $this->debug('find query options');

            if (isset($matches['host']) && strpos($matches['host'], 'kinozal') === false) {
                $this->debug('find single mode');
                $this->singleMode = true;
                return (false);
            }

            // restore query
            $query = preg_replace('/(\[opt:\S+\])?(.*)/is', '$2', $query);
        }

        $this->query = urlencode(iconv('UTF-8', 'cp1251', $query));
        curl_setopt($curl, CURLOPT_URL, sprintf($this->queryUrl, $this->query, $this->numPages - 1));
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        // count max loading pages
        $this->numPages = (isset($matches['page']) ? (int)$matches['page'] : $this->numPages);

        if (!empty($username) && !empty($password)) {
            $this->debug('verify account ' . $this->VerifyAccount($username, $password) ? 'success' : 'failure');

            curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookiePath);
            curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookiePath);
        }

        $this->debug('request url ' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));
        return (true);
    }

    /**
     * Check auth account to tracker.
     *
     * @param string $username Username for auth
     * @param string $password Password for auth
     * @access public
     * @return bool
     */
    public function VerifyAccount($username, $password)
    {
        if (empty($this->query) && file_exists($this->cookiePath)) {
            $this->debug('cookie clean');
            unlink($this->cookiePath);
        }

        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, $this->loginUrl);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt(
            $curl,
            CURLOPT_POSTFIELDS,
            http_build_query(
                array(
                    'username' => preg_replace('/(\[opt:\S+\])?(.*)/is', '$2', $username),
                    'password' => $password
                )
            )
        );

        $this->debug('verify account ' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));
        $content = curl_exec($curl);
        curl_close($curl);

        return ($this->getQuery($content)->find('.content .bx1 .red.b')->length ? false : true);
    }

    /**
     * Search tracker pages.
     *
     * @param string $response Content tracker page
     * @access private
     * @return int
     */
    private function loadPages($response)
    {
        // total search result
        preg_match(
            '/\s+(?P<result>\d+)\s+/',
            $this->getQuery($response)->find('.content .tables1 tr:last td')->text(),
            $matches
        );

        $result   = (isset($matches['result']) && !empty($matches['result']) ? (int)trim($matches['result']) : 1);
        $numPages = (int)ceil($result / $this->getUrlsPage($response));
        $this->debug('find pages ' . $numPages);

        $numLoadPages = $numPages > $this->numPages ? $numPages - ($numPages - $this->numPages) : $numPages;
        if ($numLoadPages > 1) {
            $curl = curl_copy_handle($this->curl);
            $this->debug('request page ' . $numLoadPages);

            for ($i = 2; $i <= $numLoadPages; $i++) {
                curl_setopt($curl, CURLOPT_URL, sprintf($this->queryUrl, $this->query, $i - 1));
                $this->debug('request page url ' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));
                $this->getUrlsPage(curl_exec($curl));
            }
            curl_close($curl);
        }

        return (sizeof($this->linksPage));
    }

    /**
     * Get urls tracker pages.
     *
     * @param string $response Content tracker page
     * @access private
     * @return int
     */
    private function getUrlsPage($response)
    {
        $content   = $this->getQuery($response);
        $searchBar = $content->find('.content .bx1_0 table');

        $findNum = 0;
        foreach ($content->find('.content .t_peer tr:not(:first)') as $item) {
            $pageLink                   = $this->pagePrefix . pq($item)->find('.nam a')->attr('href');
            $this->linksPage[$pageLink] = pq($item)->append($searchBar->html());
            $findNum++;
        }

        $this->debug('find item ' . $findNum);
        return ($findNum);
    }

    /**
     * Add torrent file in list.
     *
     * @param SynoAbstract $plugin Synology abstract
     * @param string $response Content tracker page
     * @access public
     * @return int
     */
    public function parse($plugin, $response)
    {
        $this->debug('parse method run');

        $findNum = 0;
        if (!$this->singleMode && $this->loadPages($response)) {
            $curl = curl_copy_handle($this->curl);

            foreach ($this->linksPage as $pageLink => $item) {
                curl_setopt($curl, CURLOPT_URL, $pageLink);
                $this->debug('parse url ' . $pageLink);

                $content = $this->getQuery(iconv('cp1251', 'UTF-8', curl_exec($curl)));
                foreach ($content->find('.content .mn_wrap') as $node) {
                    $torrent = new SynoDLMSearchKinozalItem($pageLink, pq($item), $content, pq($node));

                    if ($torrent->getDownload() !== 'unknown') {
                        $plugin->addResult(
                            $torrent->getTitle(),
                            $torrent->getDownload(),
                            $torrent->getSize(),
                            $torrent->getDateTime(),
                            $torrent->getPage(),
                            $torrent->getHash(),
                            $torrent->getSeeds(),
                            $torrent->getLeeches(),
                            $torrent->getCategory()
                        );

                        $findNum++;
                        $this->debug('parse add ' . $torrent->getTitle());
                    } else {
                        $this->debug('parse fail ' . $torrent->getTitle());
                    }
                }
            }
            curl_close($curl);
        }

        $this->debug('parse find ' . $findNum);
        return ($findNum);
    }
}
