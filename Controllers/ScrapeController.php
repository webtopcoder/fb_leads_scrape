<?php

namespace KLD\Controllers;

use KLD\Util\Scraper;
use KLD\Util\Page;
use KLD\Util\CurlUtil;

class ScrapeController
{
    /**
     * route incoming request baesd on type (cli or web)
     * @return void
     */
    public function handleRequest(): void
    {
        php_sapi_name() === 'cli' ? $this->cliRequest() : $this->webRequest();
    }

    /**
     * handle request coming from the cli
     * @return void
     */
    private function cliRequest(): void
    {
        //validate arguments / show usage
        global $argv;
        if (!in_array(count($argv), [3, 4])) { //[0] is script name
            echo "Usage: php scrape.php url pageLimit [json: true|false]\n";
            echo "Eg: php scrape.php http://insecure.com 5\n";
            echo "Eg: php scrape.php https://secure.com 5 true\n";
            die('Invalid arguments.');
        }

        //parse arguments
        $url = CurlUtil::prependScheme($argv[1]);
        $pageLimit = intval($argv[2]);

        //trigger scrape
        $result = $this->scrapeTarget($url, $pageLimit);

        //output
        if (empty($argv[3])) { //regular output
            print_r($result);
        } else { //json output
            echo json_encode($result, JSON_PRETTY_PRINT);
        }
    }

    /**
     * handle request coming from the web
     * @return void
     */
    private function webRequest(): void
    {
        //deal w/ CORS
        header("Access-Control-Allow-Origin: *");

        //validate arguments / show errors
        if (empty($_REQUEST['url'])) {
            die('You must provide a "url" argument (GET/POST)');
        }
        if (empty($_REQUEST['limit'])) {
            die('You must provide a maximum page "limit" argument (GET/POST)');
        }

        //parse arguments
        $url = CurlUtil::prependScheme($_REQUEST['url']);
        $pageLimit = intval($_REQUEST['limit']);

        //trigger scrape
        $result = $this->scrapeTarget($url, $pageLimit);

        //return response as JSON
        header('Content-type: application/json; charset=utf-8');
        echo json_encode($result, JSON_PRETTY_PRINT);
    }


    /**
     * handle the scrape operation
     * @param  string $url       target to scrape
     * @param  int    $pageLimit max # of pages to scrape
     * @return array
     */
    private function scrapeTarget(string $url, int $pageLimit): array
    {
        //init our scraper
        $scraper = new Scraper($url);

        //will contain [title, url, response_code, load_time, word_count] for client-side table
        $pageListing = [];

        //iterate scraped pages & track values
        $pages = $scraper->scrape($pageLimit);
        foreach ($pages as $page) {
            $pageListing[] = [
                'title' => $page->getTitle(),
                'url' => $page->getUrl(),
                'status_code' => $page->getStatusCode(),
                'load_time' => $page->getLoadTime(),
                'word_count' => $page->getWordCount()
            ];
        }

        //format & return output, including  pageListing & links
        $output = [
            'pages' => $pageListing,
            'links' => [
                'internal' => $scraper->getResource('internal_links'),
                'external' => $scraper->getResource('external_links')
            ]
        ];
        return $output;
    }
}
