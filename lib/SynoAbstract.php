<?php

/**
 * Synox Abstract.
 *
 * @author demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoAbstract
{

    /**
     * Lyric id.
     *
     * @var string
     * @access private
     */
    private $lyricsId;

    /**
     * Get id lyric.
     *
     * @return string
     * @access public
     */
    public function getLyricsId()
    {
        return ($this->lyricsId);
    }

    /**
     * Add torrent file in list.
     *
     * @param string $title Title torrent
     * @param string $download Url to download torrent
     * @param float $size Size files in torrent
     * @param string $datetime Date create torrent
     * @param string $page Url torrent page
     * @param string $hash Hash item
     * @param int $seeds Count torrent seeds
     * @param int $leeches Count torrent leeches
     * @param string $category Torrent category
     * @access public
     * @return void
     */
    public function addResult($title, $download, $size, $datetime, $page, $hash, $seeds, $leeches, $category)
    {
        var_dump(
            [
                'result' => [
                    'title'    => $title,
                    'download' => $download,
                    'size'     => $size,
                    'datetime' => $datetime,
                    'page'     => $page,
                    'hash'     => $hash,
                    'seeds'    => $seeds,
                    'leeches'  => $leeches,
                    'category' => $category
                ]
            ]
        );
    }

    /**
     * Add torrent json in list.
     *
     * @param string $response Content
     * @param string $rersultKey Key
     * @param array $fieldmapping Mapping json
     * @access public
     * @return void
     */
    public function addJsonResults($response, $rersultKey, $fieldmapping)
    {
        var_dump(
            [
                'json' => [
                    'response'      => $response,
                    'rersult-key'   => $rersultKey,
                    'field-mapping' => $fieldmapping
                ]
            ]
        );
    }

    /**
     * Add torrent rss in list.
     *
     * @param string $response Content
     * @access public
     * @return void
     */
    public function addRssResult($response)
    {
        var_dump(
            [
                'rss' => [
                    'response' => $response
                ]
            ]
        );
    }

    /**
     * Add song in list.
     *
     * @param string $artist Artist song
     * @param string $title Title song
     * @param string $id Id song
     * @param string $partialLyrics Partial lyric song
     * @access public
     * @return void
     */
    public function addTrackInfoToList($artist, $title, $id, $partialLyrics)
    {
        var_dump(
            [
                'track-info' => [
                    'artist'        => $artist,
                    'title'         => $title,
                    'id'            => $id,
                    'partialLyrics' => $partialLyrics
                ]
            ]
        );
        $this->lyricsId = $id;
    }

    /**
     * Add lyrics in list.
     *
     * @param string $lyric Lyric content
     * @param string $id Lyric id
     * @access public
     * @return void
     */
    public function addLyrics($lyric, $id)
    {
        var_dump(
            [
                'lyrics' => [
                    'lyric' => $lyric,
                    'id'    => $id
                ]
            ]
        );
    }
}