# php-curl-scraper

A simple web scraper written using PHP + cURL. Lets you crawl a website and extract info. Can be used via the CLI or web.

This is essentially a MVP/starting point that could be used for a specific objective, given a bit of tweaking/expansion.

## Usage

### Generic

* **url**: url to start scraping from, will assume http:// if scheme isn't provided
* **limit**: maximum number of pages to scrape before stopping

### CLI

* php scrape.php url pageLimit [json: true|false]
* php scrape.php http://insecure.com 5
* php scrape.php https://secure.com 5 true

Outputs via print_r unless 3rd argument is true, then it prints JSON.

### WEB

* .../scrape.php?url=example.com&limit=5

Outputs JSON.

## Caveats

* Link parsing ignores query params and hashes
* "/" and "/index.ext" are currently treated as two different pages
* Error handling is pretty minimal
* *Page* class can be expanded to extract additional info from scraped pages, then accessed via the *ScrapeController*
* You can set a specific user agent string in *CurlUtil*

## Testing

test/ folder contains a set of basic pages to crawl

## License

ISC