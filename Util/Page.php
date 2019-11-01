<?php

namespace KLD\Util;

class Page
{
    private $DOM;
    private $info;

    /**
     * construct a new Page object from a curl result, via curlUtil
     * @param array $curlResult make up of [content => ..., info => ...], @see CurlUtil::requestPage()
     */
    public function __construct(array $curlResult)
    {
        $this->DOM = new \DOMDocument;
        //suppressing warnings w/ '@' prefix
        //see: https://stackoverflow.com/questions/6090667/php-domdocument-errors-warnings-on-html5-tags
        @$this->DOM->loadHTML($curlResult['content']);
        $this->info = $curlResult['info'];
    }

    /**
     * return links (excluding blanks, anchors, and JS) from the page
     * @return array
     */
    public function getLinks(): array
    {
        $links = [];
        $tags = $this->DOM->getElementsByTagName('a');

        foreach ($tags as $tag) {
            $href = trim($tag->getAttribute('href'));

            //skip anchors, empty links, and javascript segments
            if (empty($href) || substr($href, 0, 1) === '#' || substr(strtolower($href), 0, 11) === 'javascript:') {
                continue;
            }

            $links[] = $href;
        }

        return $links;
    }

    /**
     * count the number of words in the page, for a given list of tags
     * @return int
     */
    public function getWordCount(array $textTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'li']): int
    {
        $words = 0;

        //find all our instances of the above target tags and get their word count
        foreach ($textTags as $textTag) {
            $tags = $this->DOM->getElementsByTagName($textTag);
            foreach ($tags as $tag) {
                $words += str_word_count($tag->textContent);
            }
        }

        return $words;
    }

    /**
     * return Page's title
     * @return int
     */
    public function getTitle(): string
    {
        $title = $this->DOM->getElementsByTagName('title');
        if ($title->length > 0) {
            return $title->item(0)->textContent;
        }
        return '';
    }

    //various get... functions for metadata from the curl info object
    public function getStatusCode(): int
    {
        return $this->info['http_code'];
    }
    public function getLoadTime(): float
    {
        return round($this->info['total_time'], 2); //stored in seconds
    }
    public function getUrl(): string
    {
        return $this->info['url'];
    }
}
