<?php

namespace KLD\Util;

use KLD\Util\Page;
use KLD\Util\CurlUtil;

class Scraper
{
    private $targetUrl;
    private $visited = [];
    private $internalLinks = [];
    private $externalLinks = [];

    /**
     * construct a Scraper for a given target
     * @param string $targetUrl
     */
    public function __construct(string $targetUrl)
    {
        $this->targetUrl = $targetUrl;
    }

    /**
     * Scrape up to a certain number of pages from the target
     * @param  int|integer $pageLimit
     * @return array<Page>
     */
    public function scrape(int $pageLimit = 1): array
    {
        //init vars
        $pages = [];

        //loop our scraper until we hit $pageLimit, or run out of unique URLs to visit
        for ($i = 0; $i < $pageLimit; $i++) {
            //figure out our next page to scrape, being mindful of urls we've already visited
            $url = $this->getNextUrl();

            //abort loop if there's no other internal links to scrape
            if (!$url) {
                break;
            }

            //scrape our page
            $pages[] = new Page(CurlUtil::requestPage($url));

            //mark link as visited so we don't re-visit going forward
            $this->visited[rtrim($url, '/')] = true;

            //get internal/external links from the page
            //also lets future getNextUrl() calls to work properly
            $this->siftLinks($pages[$i]->getLinks(), $url);
        }

        return $pages;
    }

    /**
     * get the next URL to scrape, being mindful to not re-visit URLs
     * will hit the target URL if it hasn't been yet
     * @return string
     */
    private function getNextUrl()
    {
        //we haven't visted anywhere yet, so hit the target URL
        if (empty($this->visited)) {
            return $this->targetUrl;
        }

        //subsequent crawls
        foreach ($this->internalLinks as $link) {
            //TODO: compensate for ".../" vs ".../index.ext" being equivalent, it's a bit awkward
            if (empty($this->visited[rtrim($link, '/')])) {
                return $link;
            }
        }

        return false;
    }

    /**
     * sift links as internal/external
     * @param  array  $links
     * @param  string $relativeTo
     */
    private function siftLinks(array $links, string $relativeTo): void
    {
        $internal = [];
        $external = [];

        foreach ($links as $url) {
            //convert to absolute urls
            $url = CurlUtil::absoluteUrl($url, $relativeTo);

            //sift into internal/external links
            if (parse_url($url)['host'] === parse_url($this->targetUrl)['host']) {
                $internal[] = $url;
            } else {
                $external[] = $url;
            }
        }

        //record our findings, being mindful to not create duplicates
        //this could likely be optimized w/ hashes, we'll leave it explicit for now
        $this->internalLinks = array_unique(array_merge($this->internalLinks, $internal));
        $this->externalLinks = array_unique(array_merge($this->externalLinks, $external));
    }

    /**
     * return a sorted resource from scraping results
     * @param  string $resource internal_links or external_links
     * @return array
     */
    public function getResource(string $resource): array
    {
        switch ($resource) {
            case 'internal_links':
                $resource = $this->internalLinks;
                break;
            case 'external_links':
                $resource = $this->externalLinks;
                break;
            default:
                die("Invalid resource requested: $resource");
        }

        sort($resource);
        return $resource;
    }
}
