<?php

//Defining constants
define("RSS_FEED_FILE", 'data/feeds.txt');
define("PARSED_FEED_FULLPATH", 'output/feeds.txt');
define("PARSED_RSS_FIELD_DELIMITER", '|');
define("PARSED_RSS_LINEBREAK", chr(13) . chr(10));
define("FEED_DATE_FORMAT", 'Y-m-d H:i:s');

/**
 * Class FileReader
 *
 * Reads a list of urls.
 *
 */
class FileReader
{

    private $file;

    public function __construct($filename)
    {
        if (!is_file($filename)) {
            echo "<h4>The file could not be opened</h4>";
            exit;
        }
        $this->file = $filename;
    }

    public function readFile()
    {
        $handle = fopen($this->file, 'r');
        $fileList = array();
        while (($data = trim(fgets($handle))) != false) {
            if (filter_var($data, FILTER_VALIDATE_URL) !== false) {
                $fileList[] = $data;
            }
        }
        fclose($handle);

        return $fileList;
    }
}

/**
 * Class Feed
 *
 * Object wrapper for SimplePie, initializes a SimplePie object and fetches the data from an URL.
 */
class Feed
{
    private $url;
    private $feed;

    public function __construct($url)
    {
        $this->url = $url;
        $this->feed = new SimplePie();
        $this->feed->set_feed_url($url);
        $this->feed->enable_cache(false);
        $this->feed->init();
        $error = $this->feed->error();
        if (!empty($error)) {
            echo "<h3>Feed error: " . $error . "</h3>";
        }
    }


    public function getFeed()
    {
        return $this->feed;
    }

}

/**
 * Class FeedReader
 *
 * Helper class used to initiate feed retrieval in the main class and getting the data
 */
class FeedReader
{
    /**
     * @var Feed
     */
    private $feed;
    /**
     * @var SimplePie
     */
    private $data;

    public function init($url)
    {
        if (!is_null($this->feed)) {
            unset($this->feed);
        }
        $this->feed = new Feed($url);
    }

    public function getFeedData()
    {
        $this->data = $this->feed->getFeed();
        return $this->data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getItems($url)
    {
        $this->init($url);
        $this->getFeedData();
        return $this->data->get_items();
    }

    public function getItemQuantity()
    {
        return $this->data->get_item_quantity();
    }

    public function __destruct()
    {
        unset($this->feed);
    }

    /**
     * Reads fields in a Feed through Simple Pie,
     * refer to the Simple Pie API for which fields can be retrieved.
     *
     * @param $item SimplePie_Item
     * @return array
     */
    public static function readFeedItem($item)
    {
        return array(
            $item->get_title(),
            $item->get_link(),
            $item->get_date(FEED_DATE_FORMAT),
        );
    }

    public static function stringifyFeedItemFields(Array $fields)
    {
        return implode(PARSED_RSS_FIELD_DELIMITER,
            array_map('html_entity_decode', $fields));
    }
}

/**
 * Class FeedWriter
 *
 * Writes feed info to disk
 */
class FeedWriter
{

    private $parsedFilename;
    private $fileHandle;

    public function __construct($parsedFileName)
    {
        $this->parsedFilename = $parsedFileName;
        $this->fileHandle = fopen($parsedFileName, "w");
    }

    public function __destruct()
    {
        fclose($this->fileHandle);
    }

    public function writeFeed($data)
    {
        if (!empty($data)) {
            fwrite($this->fileHandle, $data . PARSED_RSS_LINEBREAK);
        }
    }


}