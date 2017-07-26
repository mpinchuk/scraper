<?php

namespace Mpinchuk\Scraper;

use Symfony\Component\DomCrawler\Crawler;
use RuntimeException;
use GuzzleHttp\Client;

class GRBJ
{
    private $httpClient;
    private $sourceUrl;

    public function __construct()
    {
        $this->httpClient = new Client();
        $this->sourceUrl = 'http://archive-grbj-2.s3-website-us-west-1.amazonaws.com/';
    }

    public function scrap()
    {
        $res = $this->httpClient->request('GET', $this->sourceUrl);
        $pageBody = $res->getBody()->getContents();

        $crawler = new Crawler($pageBody);
        $filter = $crawler->filter('div.record');

        $result = [];
        if (iterator_count($filter) > 1) {
            foreach ($filter as $key => $content) {
                $article = new Crawler($content);
                if($article->filter('div.author')->count()) {
                    $authorName = $this->getAuthorName($article);
                    $authorKey = str_replace(' ','_',strtolower($authorName));
                    $result[$authorKey]['authorName'] = $authorName;
                    $result[$authorKey][$key] = [
                        'articleTitle' => $this->getTitle($article),
                        'articleUrl' => $this->getUrl($article),
                        'articleDate' => $this->getDate($article),
                    ];
                }
            }
        } else {
            throw new RuntimeException('Got empty result');
        }

        return $result;
    }

    private function getAuthorName(Crawler $article)
    {
        return str_replace('By ','',$article->filter('div.author')->text());
    }

    private function getTitle(Crawler $article)
    {
        return trim($article->filter('h2.headline')->text());
    }

    private function getUrl(Crawler $article)
    {
        $url = '';
        if($article->filter('h2.headline > a.url')->count()){
            $url = $article->filter('h2.headline > a.url')->attr('href');
        }
        elseif ($article->filter('h3.headline > a')->count()){
            $url = $article->filter('h3.headline > a')->attr('href');
        }
        return $url;
    }

    private function getDate(Crawler $article)
    {
        $url = $this->getUrl($article);
        $res = $this->httpClient->request('GET', $this->sourceUrl.$url);
        $pageBody = $res->getBody()->getContents();
        $crawler = new Crawler($pageBody);
        $dateNode = $crawler->filter('div.record div.meta div.date');
        $dateValue = null;
        if($dateNode->count()){
            $dateValue = $dateNode->text();
        }
        return $dateValue;
    }
}
