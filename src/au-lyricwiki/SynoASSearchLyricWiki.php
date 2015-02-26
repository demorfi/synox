<?php

if (!extension_loaded('mbstring')) {
    dl('mbstring.so');
}

/**
 * Synology Audio Station Search Song Text.
 * For search song text to lyrics.wikia.com.
 *
 * @author demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoASSearchLyricWiki
{

    /**
     * Log file.
     *
     * @var string
     * @access private
     */
    private $logsPath = '/tmp/au-lyricwiki.log';

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
    protected $pagePrefix = 'http://lyrics.wikia.com';

    /**
     * Query send.
     *
     * @var string
     * @access protected
     */
    protected $queryUrl = 'http://lyrics.wikia.com/api.php?artist=%s&song=%s&fmt=realjson';

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
     * Get full name artist.
     *
     * @param string $artist Artist song
     * @access private
     * @return bool
     */
    private function getArtistFullName($artist)
    {
        $this->debug('search artist full name');

        $curl = curl_copy_handle($this->curl);
        curl_setopt($curl, CURLOPT_URL, sprintf($this->queryUrl, urlencode($artist), ''));
        $this->debug('request url ' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));

        $content = json_decode(
            preg_replace_callback(
                '/\\\\u([0-9a-fA-F]{4})/',
                function ($match) {
                    return (mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE'));
                },
                curl_exec($curl)
            ),
            true
        );

        return (isset($content['artist'], $content['albums']) && !empty($content['albums']) ? $content['artist'] : false);
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

        if (($artist = $this->getArtistFullName($artist)) !== false) {
            $this->debug('find artist ' . $artist);

            $curl = curl_copy_handle($this->curl);
            curl_setopt($curl, CURLOPT_URL, sprintf($this->queryUrl, urlencode($artist), urlencode($title)));
            $this->debug('request url ' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));

            $content = json_decode(
                iconv(
                    'UTF-8',
                    'ISO-8859-1',
                    preg_replace_callback(
                        '/\\\\u([0-9a-fA-F]{4})/',
                        function ($match) {
                            return (mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UTF-16BE'));
                        },
                        curl_exec($curl)
                    )
                ),
                true
            );

            if (isset($content['lyrics']) && $content['lyrics'] !== 'Not found') {
                $this->debug('lyrics found song ' . $content['url']);
                $plugin->addTrackInfoToList($content['artist'], $content['song'], $content['url'], $content['lyrics']);
                return (1);
            }
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
            curl_setopt($curl, CURLOPT_URL, $id . '?action=edit');
            $this->debug('request url ' . curl_getinfo($curl, CURLINFO_EFFECTIVE_URL));

            // get content in tags <lyrics>
            preg_match(
                '/<lyrics>(?P<lyrics>.*)<\/lyrics>/is',
                html_entity_decode(curl_exec($curl), ENT_QUOTES, 'UTF-8'),
                $content
            );

            if (isset($content['lyrics']) && !empty($content['lyrics'])) {
                $this->debug('lyrics found song content ' . $id);
                $plugin->addLyrics(trim(strip_tags($content['lyrics'])), $id);
                return (true);
            }
        }

        return (false);
    }
}
