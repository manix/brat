<?php

use Manix\Brat\Helpers\URL;

namespace Manix\Brat\Helpers;

class HTMLGenerator {

    public function __call($name, $arguments) {
        $name = html($name);
        $content = isset($arguments[0]) ? html($arguments[0]) : null;
        $attributes = isset($arguments[1]) ? $arguments[1] : array();

        return '<' . $name . $this->parseAttributes($attributes) . '>' . $content . '</' . $name . '>';
    }

    public function meta($name, $content, array $attributes = array()) {
        if ($name === null) {
            unset($attributes['name']);
        } else {
            $attributes['name'] = $name;
        }
        $attributes['content'] = $content;

        return '<meta' . $this->parseAttributes($attributes) . '/>';
    }

    public function script($src, array $attributes = array(), $defer = true) {
        $attributes['type'] = 'text/javascript';
        $attributes['src'] = $this->parseURL($src);
        if ($defer) {
            $attributes['defer'] = 'defer';
        }

        return '<script' . $this->parseAttributes($attributes) . '></script>';
    }

    public function stylesheet($src, array $attributes = array()) {
        $attributes['type'] = 'text/css';
        $attributes['href'] = $this->parseURL($src);
        $attributes['rel'] = 'stylesheet';

        return '<link' . $this->parseAttributes($attributes) . '/>';
    }

    public function a($url, $text = null, $target = null, array $attributes = array()) {

        $attributes['href'] = $url;

        if ($target !== null) {
            $attributes['target'] = $target;
        }

        if ($text === null) {
            $text = $attributes['href'];
        }

        return '<a' . $this->parseAttributes($attributes) . '>' . html($text) . '</a>';
    }

    public function img($src, $alt, array $attributes = array()) {
        $attributes['src'] = $this->parseURL($src);
        $attributes['alt'] = $alt;

        return '<img' . $this->parseAttributes($attributes) . '/>';
    }

    public function formOpen(array $attributes = array()) {
        return '<form' . $this->parseAttributes($attributes) . '>';
    }

    public function input($name, $type = 'text', $value = null, array $attributes = array()) {
        if ($name) {
            $attributes['name'] = $name;
        }

        if ($type) {
            $attributes['type'] = $type;
        }

        if ($value) {
            $attributes['value'] = $value;
        }

        return '<input' . $this->parseAttributes($attributes) . '/>';
    }

    public function select($options, array $attributes = array(), $selected = null) {
        $html = '';

        foreach ($options as $key => $value) {
            $html .= '<option value="' . html($key) . '"';

            if ($key == $selected) {
                $html .= ' selected="selected"';
            }

            $html .= '>' . html($value) . '</option>';
        }

        return '<select' . $this->parseAttributes($attributes) . '>' . $html . '</select>';
    }

    public function ul(array $items, array $attributes = array()) {

        return '<ul' . $this->parseAttributes($attributes) . '><li>' . join('</li><li>', array_map('html', $items)) . '</li></ul>';
    }

    public function ol(array $items, array $attributes = array()) {

        return '<ol' . $this->parseAttributes($attributes) . '><li>' . join('</li><li>', array_map('html', $items)) . '</li></ol>';
    }

    private function parseAttributes($attributes) {
        $string = '';

        foreach ($attributes as $attribute => $value) {
            $string .= ' ' . html($attribute) . '="' . html($value) . '"';
        }

        return $string;
    }

    private function parseURL($url) {
        return (new URL($url))->absolute();
    }

    public function minify($html) {
        $pres = null;

        $html = preg_replace('/[\r\n]\<\/code\>/', '</code>', $html);
        preg_match_all('/\<pre.*?\>(.*?)\<\/pre\>/s', $html, $pres);

        $pres = empty($pres[0]) ? [] : $pres[0];

        $html = preg_replace([
            // '/(?<!\:)\/\/.*/',
            '/\/\*.*?\*\//s',
            '/\s{2,}/',
            '/\t/',
            '/[\r\n]/',
        ], [
            // '',
            '',
            ' ',
            '',
            ''
        ], $html);

        if (!empty($pres)) {
            $c = 0;
            $start = 0;

            while (($start = strpos($html, '<pre', $start)) !== false) {
                $end = strpos($html, '</pre>', $start) + 6;

                if (!isset($pres[$c])) {
                    break;
                }

                $html = substr_replace($html, $pres[$c], $start, $end - $start);
                $c++;
                $start++;
            }
        }

        return str_replace('<?php', '<?php ', $html);
    }

    /**
     * Sets OG, Twitter and ordinary meta tags for bots and crawlers.
     * 
     * @param array $data Available keys: title, author, keywords, description, image, robots
     */
    public function SEO(array $data) {
        $output = '';

        if (isset($data['title'])) {
            $output .= $this->title($data['title']) .
            $this->meta(null, $data['title'], ['property' => 'og:title']) .
            $this->meta('twitter:title', $data['title']) .
            $this->meta('title', $data['title']);
        }

        $url = url();

        $output .= $this->meta('og:url', $url) .
        $this->meta('twitter:url', $url) .
        $this->meta(null, SITE_NAME, ['property' => 'og:site_name']) .
        $this->meta(null, 'article', ['property' => 'og:type']) .
        $this->meta('twitter:card', 'summary') .
        $this->meta('robots', isset($data['robots']) ? $data['robots'] : 'index,follow');

        if (isset($data['author'])) {
            $output .= $this->meta('author', $data['author']);
        }

        if (isset($data['keywords'])) {
            $output .= $this->meta('keywords', $data['keywords']);
        }

        if (isset($data['description'])) {
            $output .= $this->meta('description', $data['description']) .
            $this->meta(null, $data['description'], ['property' => 'og:description']) .
            $this->meta('twitter:description', $data['description']);
        }

        if (isset($data['image'])) {
            $output .= $this->meta(null, $data['image'], ['property' => 'og:image']) .
            $this->meta('twitter:image', $data['image']);
        }

        return $output;
    }

    /**
     * Print a date or datetime string beautifully using fontawesome icons.
     * 
     * @param string $string The datetime string - must follow format 
     * "YYYY-mm-dd H:i:s" or just "YYYY-mm-dd"
     * @param string $separator A string that will be used to separate the date and time values.
     * @param string $date_separator A string that will be used to separate components of the date.
     * @return string
     */
    public function datetime($string, $separator = ' ', $date_separator = null) {
        $date = substr($string, 0, 10);
        $time = substr($string, 11);

        if ($date_separator !== null) {
            $date = str_replace('-', $date_separator, $date);
        }

        $string = '<i class="fa fa-calendar"></i> ' . $date;

        if ($time) {
            $string .= $separator . '<i class="fa fa-clock-o"></i> ' . $time;
        }

        return $string;
    }

    /**
     * Generate breadcrumbs.
     * 
     * Both parameters represent routes and thus MUST NOT start or end 
     * with a forward slash "/".
     * 
     * @param string $base The base which should be ignored when constructing the breadcrumbs.
     * @param string $route The route which should be used to construct the breadcrumbs.
     * @return string HTML output.
     */
    public function breadcrumbs($base = '', $route = null) {
        if ($route === null) {
            $route = ROUTE;
        }

        if ($base && strpos($route, $base) === 0) {
            $route = substr($route, strlen($base));
        }

        $route = '/' . $route . '/';

        $crumbs = '';
        $last = 0;
        $length = strlen($route);
        $link = $base;

        $search = ['-', '_'];
        $replace = ' ';

        for ($i = 1; $i < $length; $i++) {
            if ($route[$i] === '/') {
                $current = substr($route, $last + 1, $i - $last - 1);
                $link .= '/' . $current;

                $crumbs .= $this->a($link, str_replace($search, $replace, $current)) . ' / ';

                $last = $i;
            }
        }

        return '<div class="breadcrumbs">' . substr($crumbs, 0, -3) . '</div>';
    }

}
