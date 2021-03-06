<?php

namespace Square1\Resized;

class Resized
{
    /**
     * @var string
     */
    private $key;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var string
     */
    private $host = 'https://img.resized.co';

    /**
     * @var string
     */
    private $defaultImage = 'https://img.resized.co/no-image.png';

    /**
    * Constructor
    *
    * @param string $key
    * @param string $secret
    */
    public function __construct($key, $secret)
    {
        if (strlen($secret) != 47) {
            throw new \InvalidArgumentException('Invalid Secret');
        }
        
        $this->key = $key;
        $this->secret = $secret;
    }

    /**
    * Set host name
    *
    * @param string $url
    */
    public function setHost($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('Invalid Host URL');
        }

        $this->host = $url;
    }

    /**
    * Set default image url
    *
    * @param string $url
    */
    public function setDefaultImage($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new \InvalidArgumentException('Invalid Default Image URL');
        }

        $this->defaultImage = $url;
    }

    /**
    * Process image
    *
    * @param string $url
    * @param int    $width
    * @param int    $height
    * @param string $title
    *
    * @param string
    */
    public function process($url, $width = '', $height = '', $title = '')
    {
        //If invalid URL passed, set to default image
        if (empty($url) || filter_var($url, FILTER_VALIDATE_URL) === false) {
            $url = $this->defaultImage;
        }

        $data = json_encode([
            'url' => $url,
            'width' => $width,
            'height' => $height,
            'default' => $this->defaultImage
        ]);

        $uri = base64_encode(json_encode([
            'data' => $data,
            'hash' => sha1($this->key.$this->secret.$data)
        ]));

        // Make the b64 string url-safe
        $uri = str_replace(['+', '/'], ['-', '_'], $uri);

        $fullUrl = [
            $this->host,
            $this->key,
            $uri,
            $this->filename($url, $title)
        ];

        return implode('/', $fullUrl);
    }


    /**
     * Get seo slug and file extention
     *
     * @param string $url
     * @param string $title
     *
     * @return string|null
     */
    private function filename($url, $title = '')
    {
        if (!empty($title)) {
            $filename = $this->slug($title);
        } else {
            $filename = $this->slug(pathinfo($url, PATHINFO_FILENAME));
        }

        $extention = pathinfo($url, PATHINFO_EXTENSION);
        if (!empty($extention)) {
            return $filename.'.'.$extention;
        }

        return $filename;
    }

     /**
     * Generate a URL friendly "slug" from a given string.
     *
     * @param string $str
     *
     * @return string
     */
    private function slug($str)
    {
        // replace non letter or digits by -
        $str = preg_replace('~[^\\pL\d]+~u', '-', $str);

        $str = trim($str, '-');

        // transliterate
        $str = iconv('utf-8', 'us-ascii//TRANSLIT', $str);

        $str = strtolower($str);

        // remove unwanted characters
        $str = preg_replace('~[^-\w]+~', '', $str);

        return $str;
    }
}
