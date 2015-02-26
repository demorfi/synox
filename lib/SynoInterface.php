<?php

/**
 * Synox Interface.
 *
 * @author demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
interface SynoInterface
{

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
    public function prepare($curl, $query, $username = null, $password = null);

    /**
     * Add torrent file in list.
     *
     * @param SynoAbstract $plugin Synology abstract
     * @param string $response Content tracker page
     * @access public
     * @return int
     */
    public function parse($plugin, $response);

    /**
     * Get information download torrent.
     *
     * @access public
     * @return array
     */
    public function GetDownloadInfo();

    /**
     * Search lyrics.
     *
     * @param string $artist Artist song
     * @param string $title Title song
     * @param SynoAbstract $plugin Synology abstract
     * @access public
     * @return int
     */
    public function getLyricsList($artist, $title, $plugin);

    /**
     * Add lyrics.
     *
     * @param string $id Id found lyric
     * @param SynoAbstract $plugin Synology abstract
     * @access public
     * @return bool
     */
    public function getLyrics($id, $plugin);
}