<?php

/**
 * Synology Download Station Search File.
 * For search broadcast to youtube.com.
 *
 * @author demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoDLMSearchYouTubeItem extends SynoDLMSearchYouTube
{

    /**
     * Content file element.
     *
     * @var array
     * @access private
     */
    private $node;

    /**
     * Content root element.
     *
     * @var array
     * @access private
     */
    private $item;

    /**
     * Host name.
     *
     * @var string
     * @access private
     */
    private $host;

    /**
     * Title file.
     *
     * @var string
     * @access private
     */
    private $title;

    /**
     * Url to download file.
     *
     * @var string
     * @access private
     */
    private $download;

    /**
     * File category.
     *
     * @var string
     * @access private
     */
    private $category;

    /**
     * Url current page.
     *
     * @var string
     * @access private
     */
    private $pageLink;

    /**
     * Date create file.
     *
     * @var string
     * @access private
     */
    private $datetime;

    /**
     * Size file.
     *
     * @var float
     * @access private
     */
    private $size;

    /**
     * Count file seeds.
     *
     * @var int
     * @access private
     */
    private $seeds;

    /**
     * Count file leeches.
     *
     * @var int
     * @access private
     */
    private $leeches;

    /**
     * Initialize parser.
     *
     * @param string $pageLink Url current page
     * @param array $item Content root element
     * @param array $node Content file element
     * @access public
     */
    public function __construct($pageLink, array $item, array $node)
    {
        $this->debug('item init');

        $this->pageLink = $pageLink;
        $this->item     = $item;
        $this->node     = $node;

        list(, $this->host) = array_values(parse_url($this->pagePrefix));
    }

    /**
     * Get file title.
     *
     * @access public
     * @return string
     */
    public function getTitle()
    {
        if (empty($this->title)) {
            $title       = (isset($this->item['title']['$t']) ? $this->item['title']['$t'] : '');
            $translation = isset($this->node['media$credit'][0]['yt$display'], $this->node['media$description']['$t'])
                ? $this->node['media$credit'][0]['yt$display'] . ' - ' . $this->node['media$description']['$t'] : '';

            $this->title = !empty($title)
                ? trim($title) . (!empty($translation)
                    ? ' [' . trim($translation) : ']') . ' [' . $this->host . ']' : 'unknown';
            $this->debug('item find title ' . $this->title);
        }

        return ($this->title);
    }

    /**
     * Get url to download file.
     *
     * @access public
     * @return string
     */
    public function getDownload()
    {
        if (empty($this->download)) {
            $download = isset($this->node['yt$videoid']['$t']) ? $this->node['yt$videoid']['$t'] : '';

            $this->download = (!empty($download) ? $this->pagePrefix . $download : 'unknown');
            $this->debug('item find download ' . $this->download);
        }

        return ($this->download);
    }

    /**
     * Get category file.
     *
     * @access public
     * @return string
     */
    public function getCategory()
    {
        if (empty($this->category)) {
            $category = isset($this->node['media$category'][0]['label']) ? $this->node['media$category'][0]['label'] : '';

            $this->category = (!empty($category) ? trim($category) : 'unknown');
            $this->debug('item find category ' . $this->category);
        }

        return ($this->category);
    }

    /**
     * Get url file page.
     *
     * @access public
     * @return string
     */
    public function getPage()
    {
        return ($this->pageLink);
    }

    /**
     * Get hash.
     *
     * @access public
     * @return string
     */
    public function getHash()
    {
        return (md5($this->pageLink . $this->getTitle() . $this->getDownload()));
    }

    /**
     * Get create file date.
     *
     * @access public
     * @return string
     */
    public function getDateTime()
    {
        if (empty($this->datetime)) {
            $date = isset($this->item['published']['$t']) ? $this->item['published']['$t'] : '';

            $this->datetime = (!empty($date) ? date('d-m-Y H:i', strtotime($date)) : '01-01-1970 00:00');
            $this->debug('item find datetime ' . $this->datetime);
        }

        return ($this->datetime);
    }

    /**
     * Get size file.
     *
     * @access public
     * @return float
     */
    public function getSize()
    {
        if (empty($this->size)) {
            $this->size = isset($this->node['yt$duration']['seconds'])
                ? ((float)$this->node['yt$duration']['seconds'] * 1024) : 0;
            $this->debug('item find size ' . $this->size);
        }

        return ((float)$this->size);
    }

    /**
     * Get count file seeds.
     *
     * @access public
     * @return int
     */
    public function getSeeds()
    {
        if (empty($this->seeds)) {
            $seeds = isset($this->item['gd$comments']['gd$feedLink']['countHint'])
                ? $this->item['gd$comments']['gd$feedLink']['countHint'] : 0;

            $this->seeds = (!empty($seeds) ? (int)trim($seeds) : 0);
            $this->debug('item find seeds ' . $this->seeds);
        }
        return ((int)$this->seeds);
    }

    /**
     * Get count file leeches.
     *
     * @access public
     * @return int
     */
    public function getLeeches()
    {
        if (empty($this->leeches)) {
            $leeches = isset($this->item['yt$statistics']['viewCount']) ? $this->item['yt$statistics']['viewCount'] : 0;

            $this->leeches = (!empty($leeches) ? (int)$leeches - $this->getSeeds() : 0);
            $this->debug('item find leeches ' . $this->leeches);
        }

        return ((int)$this->leeches);
    }
}
