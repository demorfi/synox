<?php

if (!class_exists('SynoDLMSearchYouTubeItem')) {
    include(__DIR__ . DIRECTORY_SEPARATOR . 'SynoDLMSearchYouTubeItem.php');
}

/**
 * Synology Download Station Search File.
 * For search broadcast to youtube.com.
 *
 * @author demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoDLMSearchYouTube
{

    /**
     * Log file.
     *
     * @var string
     * @access private
     */
    private $logsPath = '/tmp/bt-youtube.log';

    /**
     * Found links.
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
    protected $pagePrefix = 'http://www.youtube.com/watch?v=';

    /**
     * Query send.
     *
     * @var string
     * @access protected
     */
    protected $queryUrl = 'http://gdata.youtube.com/feeds/api/videos?q=%s&max-results=2&start-index=1&orderby=relevance&alt=json&v=2&fields=entry(title,published,media:group(media:category(@label),yt:videoid,media:description,yt:duration,media:credit(@yt:display)),gd:comments(gd:feedLink(@countHint)),yt:statistics(@viewCount))';

    /**
     * Debug mode.
     *
     * @var bool
     * @access protected
     * @static
     */
    protected static $debugMode = false;

    /**
     * @access public
     */
    public function __construct()
    {
        $this->debug('search init');
    }

    /**
     * @access public
     */
    public function __destruct()
    {
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
     * Send query.
     *
     * @param resource $curl Resource curl
     * @param string $query Search query
     * @param string $username Username for options
     * @access public
     * @return bool
     */
    public function prepare($curl, $query, $username = null)
    {
        $this->debug('prepare method run');

        // check settings in username
        if (strpos($username, '[opt:') !== false) {
            preg_match('/(\[opt:(d-(?P<debug>(\d)))?\])?/is', $username, $matches);
            self::$debugMode = isset($matches['debug']) && $matches['debug'] === '1';
            $this->debug('find username options');
        }

        // check settings in query
        if (strpos($query, '[opt:') !== false) {
            preg_match('/(\[opt:(d-(?P<debug>(\d)))?(h-(?P<host>(\S+)))?\])?/is', $query, $matches);
            self::$debugMode = isset($matches['debug']) && $matches['debug'] === '1';
            $this->debug('find query options');

            if (isset($matches['host']) && strpos($matches['host'], 'youtube') === false) {
                $this->debug('find single mode');
                $this->singleMode = true;
                return (false);
            }

            // restore query
            $query = preg_replace('/(\[opt:\S+\])?(.*)/is', '$2', $query);
        }

        $this->query = $query;
        curl_setopt($curl, CURLOPT_URL, sprintf($this->queryUrl, urlencode($this->query)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HEADER, false);

        $this->debug('request url ' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));
        return (true);
    }

    /**
     * Get urls.
     *
     * @param string $response Content feed
     * @access private
     * @return int
     */
    private function getUrlsPage($response)
    {
        $findNum = 0;

        if (isset($response['feed']['entry'])) {
            foreach ($response['feed']['entry'] as $item) {
                $pageLink                   = $this->pagePrefix . $item['media$group']['yt$videoid']['$t'];
                $this->linksPage[$pageLink] = $item;
                $findNum++;
            }
        }

        $this->debug('find item ' . $findNum);
        return ($findNum);
    }

    /**
     * Add file in list.
     *
     * @param SynoAbstract $plugin Synology abstract
     * @param string $response Content
     * @access public
     * @return int
     */
    public function parse($plugin, $response)
    {
        $this->debug('parse method run');
        $response = json_decode($response, true);

        $findNum = 0;
        if (!$this->singleMode && $this->getUrlsPage($response)) {

            foreach ($this->linksPage as $pageLink => $item) {
                $this->debug('parse url ' . $pageLink);
                $torrent = new SynoDLMSearchYouTubeItem($pageLink, $item, $item['media$group']);

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
            }
        }

        $this->debug('parse find ' . $findNum);
        return ($findNum);
    }
}
