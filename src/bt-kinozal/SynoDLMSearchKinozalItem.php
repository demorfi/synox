<?php

/**
 * Synology Download Station Search File.
 * For search torrent files to kinozal.tv.
 *
 * @author demorfi <demorfi@gmail.com>
 * @version 1.0
 * @source https://github.com/demorfi/synox
 * @license http://opensource.org/licenses/MIT Licensed under MIT License
 */
class SynoDLMSearchKinozalItem extends SynoDLMSearchKinozal
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
            $title = $this->item->find('.nam a')->text();

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
            $download = $this->node->find('.mn1_content table a[href^=/download]')->attr('href');

            $this->download = (!empty($download) ? $this->pagePrefix . $download : 'unknown');
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
            preg_match('/(?P<id>\d+)\./is', $this->item->find('.bt img')->attr('src'), $matches);
            $categories = $this->item->find('select[name="c"] option');

            $this->category = isset($matches['id']) && $categories->length
                ? trim(pq($categories)->filter('[value=' . $matches['id'] . ']')->text()) : 'unknown';
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
            preg_match(
                '/(?P<date>\d{2}\.\d{2}\.\d{4}).*(?P<time>\d{2}:\d{2})/is',
                $this->item->find('.sl_p ~ .s')->text(),
                $matches
            );

            $this->datetime = isset($matches['date'], $matches['time'])
                ? date('d-m-Y H:i', strtotime($matches['date'] . ' ' . $matches['time'])) : '01-01-1970 00:00';
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
            preg_match('/\((?P<size>(\d|,)+)\)/is', $this->node->find('.mn1_menu li .floatright')->text(), $matches);

            $this->size = (isset($matches['size']) ? (float)str_replace(',', '', $matches['size']) : 0);
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
            $seeds = $this->item->find('.sl_s')->text();

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
            $leeches = $this->item->find('.sl_p')->text();

            $this->leeches = (!empty($leeches) ? (int)trim($leeches) : 0);
            $this->debug('item find leeches ' . $this->leeches);
        }

        return ((int)$this->leeches);
    }
}
