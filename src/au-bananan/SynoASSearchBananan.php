<?php

/**
 * Synology Audio Station Translate Song Text.
 * For translate song text to bananan.org.
 *
 * @author demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoASSearchBananan
{

    /**
     * Log file.
     *
     * @var string
     * @access private
     */
    private $logsPath = '/tmp/au-bananan.log';

    /**
     * Curl resource for requests.
     *
     * @var resource
     * @access protected
     */
    protected $curl;

    /**
     * Language search lyric.
     *
     * @var string
     * @access protected
     */
    protected $language = 'ru';

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
    protected $pagePrefix = 'bananan.org';

    /**
     * Query send.
     *
     * @var string
     * @access protected
     */
    protected $queryUrl = 'https://ajax.googleapis.com/ajax/services/search/web?v=1.0&hl=%s&rsz=1&q=%ssite:bananan.org';

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
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, true);
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
     * Search lyrics.
     *
     * @param string $artist Artist song
     * @param string $title Title song
     * @param SynoAbstract $plugin Synology abstract
     * @access public
     * @return int
     */
    public function getLyricsList($artist, $title, $plugin)
    {
        $this->debug('lyrics list method run');

        $curl = curl_copy_handle($this->curl);
        $url  = sprintf($this->queryUrl, $this->language, rawurlencode('+"' . $artist . '" +"' . $title . '"'));

        curl_setopt($curl, CURLOPT_URL, $url);
        $this->debug('request url ' . rawurldecode(curl_getinfo($curl, CURLINFO_EFFECTIVE_URL)));

        $content = json_decode(curl_exec($curl), true);
        if (isset($content['responseData']['results'][0]) && !empty($content['responseData']['results'][0])) {
            $response = $content['responseData']['results'][0];
            $this->debug('lyrics found song ' . $response['cacheUrl']);
            $plugin->addTrackInfoToList($artist, $title, $response['cacheUrl'], null);
            return (1);
        }

        $this->debug('lyrics not found');
        return (0);
    }

    /**
     * Add lyrics.
     *
     * @param string $id Id found lyric
     * @param SynoAbstract $plugin Synology abstract
     * @access public
     * @return bool
     */
    public function getLyrics($id, $plugin)
    {
        $this->debug('lyrics method run');

        if (strpos($id, $this->pagePrefix) !== false) {
            $curl = curl_copy_handle($this->curl);
            curl_setopt($curl, CURLOPT_URL, $id);
            $this->debug('request url ' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));

            // get content in tags <lyrics>
            preg_match(
                '/(?<=(<td\sid="text_tr">))(?P<lyrics>.*)<\/td>/isU',
                html_entity_decode(curl_exec($curl), ENT_QUOTES, 'UTF-8'),
                $content
            );

            if (isset($content['lyrics']) && !empty($content['lyrics'])) {
                $this->debug('lyrics found song content ' . $id);
                $plugin->addLyrics(
                    trim(strtr(strip_tags($content['lyrics']), array("\n\n\n" => "\n", "\t" => ''))),
                    $id
                );
                return (true);
            }
        }

        return (false);
    }
}
