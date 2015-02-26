<?php

/**
 * Synology Download Station Search File.
 * For search torrent files to pornolab.net.
 *
 * @author demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoDLMSearchPornolabItem extends SynoDLMSearchPornolab
{

    /**
     * Content page torrent.
     *
     * @var phpQueryObject
     * @access private
     */
    private $content;

    /**
     * Content torrent element to torrent page.
     *
     * @var phpQueryObject
     * @access private
     */
    private $node;

    /**
     * Content root element torrent.
     *
     * @var phpQueryObject
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
     * Title torrent.
     *
     * @var string
     * @access private
     */
    private $title;

    /**
     * Url to download torrent.
     *
     * @var string
     * @access private
     */
    private $download;

    /**
     * Torrent category.
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
     * Date create torrent.
     *
     * @var string
     * @access private
     */
    private $datetime;

    /**
     * Size files in torrent.
     *
     * @var float
     * @access private
     */
    private $size;

    /**
     * Count torrent seeds.
     *
     * @var int
     * @access private
     */
    private $seeds;

    /**
     * Count torrent leeches.
     *
     * @var int
     * @access private
     */
    private $leeches;

    /**
     * Initialize parser.
     *
     * @param string $pageLink Url current page
     * @param phpQueryObject $item Content root element torrent
     * @param phpQueryObject $content Content page torrent
     * @param phpQueryObject $node Content torrent element to torrent page
     * @access public
     */
    public function __construct($pageLink, phpQueryObject $item, phpQueryObject $content, phpQueryObject $node)
    {
        $this->debug('item init');

        $this->pageLink = $pageLink;
        $this->item     = $item;
        $this->content  = $content;
        $this->node     = $node;

        list(, $this->host) = array_values(parse_url($this->pagePrefix));
    }

    /**
     * Get torrent title.
     *
     * @access public
     * @return string
     */
    public function getTitle()
    {
        if (empty($this->title)) {
            $title = $this->content->find('#main_content table .maintitle a:first')->text();

            $this->title = (!empty($title) ? trim($title) . ' [' . $this->host . ']' : 'unknown');
            $this->debug('item find title ' . $this->title);
        }

        return ($this->title);
    }

    /**
     * Get url to download torrent.
     *
     * @access public
     * @return string
     */
    public function getDownload()
    {
        if (empty($this->download)) {
            $download = $this->node->find('.attach a.dl-link')->attr('href');

            $this->download = (!empty($download) ? $this->pagePrefix . '/' . $download : 'unknown');
            $this->debug('item find download ' . $this->download);
        }

        return ($this->download);
    }

    /**
     * Get category torrent.
     *
     * @access public
     * @return string
     */
    public function getCategory()
    {
        if (empty($this->category)) {
            $category = $this->content->find('#main_content table table .nav a:last')->text();

            $this->category = (!empty($category) ? trim($category) : 'unknown');
            $this->debug('item find category ' . $this->category);
        }

        return ($this->category);
    }

    /**
     * Get url torrent page.
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
     * Get create torrent date.
     *
     * @access public
     * @return string
     */
    public function getDateTime()
    {
        if (empty($this->datetime)) {
            $date = $this->item->find('.row4:last u')->text();

            $this->datetime = (!empty($date) ? date('d-m-Y H:i', $date) : '01-01-1970 00:00');
            $this->debug('item find datetime ' . $this->datetime);
        }

        return ($this->datetime);
    }

    /**
     * Get size files in torrent.
     *
     * @access public
     * @return float
     */
    public function getSize()
    {
        if (empty($this->size)) {
            $this->size = (float)$this->item->find('.row4.nowrap:not(:last) u')->text();
            $this->debug('item find size ' . $this->size);
        }

        return ((float)$this->size);
    }

    /**
     * Get count torrent seeds.
     *
     * @access public
     * @return int
     */
    public function getSeeds()
    {
        if (empty($this->seeds)) {
            $seeds = $this->item->find('.seedmed b')->text();

            $this->seeds = (!empty($seeds) ? (int)trim($seeds) : 0);
            $this->debug('item find seeds ' . $this->seeds);
        }

        return ((int)$this->seeds);
    }

    /**
     * Get count torrent leeches.
     *
     * @access public
     * @return int
     */
    public function getLeeches()
    {
        if (empty($this->leeches)) {
            $leeches = $this->item->find('.leechmed b')->text();

            $this->leeches = (!empty($leeches) ? (int)trim($leeches) : 0);
            $this->debug('item find leeches ' . $this->leeches);
        }

        return ((int)$this->leeches);
    }
}
