<?php

namespace KLD\Util;

abstract class CurlUtil
{
    /**
     * use cURL to request a URL and return its content & metadata
     * @param  string $url
     * @return array       [content => ..., info => ...]
     */
    public static function requestPage(string $url): array
    {
        //create handler
        $ch = curl_init($url);

        //settings
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        //spoof user agent (ie. as a browser), if desired
        //curl_setopt($ch, CURLOPT_USERAGENT, 'UA GOES HERE');

        //send request
        $content = curl_exec($ch);

        //error handling?
        if ($content === false) {
            //TODO: be more graceful
            print_r(curl_error($ch));
            die(' --- CURL ERROR');
        }

        //get metadata like response code, load time, etc.
        $info = curl_getinfo($ch);

        return [
            'content' => $content,
            'info' => $info
        ];
    }

    /**
     * Turn a URL into an absolute URL
     * @param  string $url
     * @param  string $relativeTo
     * @return string
     */
    public static function absoluteUrl(string $url, string $relativeTo): string
    {
        //parse urls
        $url = parse_url($url);
        $relativeTo = parse_url($relativeTo);

        //fill in missing data about scheme/host re: relative urls
        if (empty($url['scheme'])) { //HTTP or HTTPS
            $url['scheme'] = $relativeTo['scheme'];
        }
        if (empty($url['host'])) { //relative link
            $url['host'] = $relativeTo['host'];

            //account for no leading slash, subfolders, etc.
            if (substr($url['path'], 0, 1) !== '/') {
                $parts = explode('/', $relativeTo['path']);
                if (count($parts) > 1) {
                    array_pop($parts);
                }
                $base = implode('/', $parts);
                $url['path'] = $base . '/' . $url['path'];
            }
        }
        if (empty($url['path'])) { //page root
            $url['path'] = '';
        }

        //TODO: handling for query params / hashes

        //format url and account for leading/trailing slashes
        $absolute = $url['host'] . '/' . trim($url['path'], '/');
        return $url['scheme'] . '://' . $absolute;
    }

    /**
     * prepend http:// scheme if none is provided
     * @param  string $url
     * @return string
     */
    public static function prependScheme(string $url): string
    {
        if (!array_key_exists('scheme', parse_url($url))) {
            $url = 'http://' . $url;
        }
        return $url;
    }
}
