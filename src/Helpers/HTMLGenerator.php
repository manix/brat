<?php

namespace Manix\Brat\Helpers;

use function html;
use function url;

class HTMLGenerator {

  /**
   * Generate an HTML element.
   * @param string $name Element name
   * @param array $arguments Array of parameters in order: <ul><li>string $content the inner html of the element</li><li>array $attributes associative array of attributes for the element</li><li>bool $ecode whether to encode $content as html or not</li></ul>
   * @return type
   */
  public function __call($name, $arguments) {
    $name = html($name);
    $content = $arguments[0] ?? null;
    $attributes = $arguments[1] ?? [];
    if (!empty($arguments[2])) {
      $content = html($content);
    }

    return '<' . $name . $this->parseAttributes($attributes) . '>' . $content . '</' . $name . '>';
  }

  /**
   * Generate a meta tag.
   * @param string $name Value for attribute "name"
   * @param string $content value for attribute "content"
   * @param array $attributes
   * @return string The meta tag.
   */
  public function meta(string $name, string $content, array $attributes = array()): string {
    if ($name === null) {
      unset($attributes['name']);
    } else {
      $attributes['name'] = $name;
    }
    $attributes['content'] = $content;

    return '<meta' . $this->parseAttributes($attributes) . '/>';
  }

  /**
   * Generate a script tag.
   * @param string $src value for the "src" attribute.
   * @param array $attributes
   * @param bool $defer Whether to include the "defer" attribute
   * @return string The script tag.
   */
  public function script(string $src, array $attributes = array(), bool $defer = true): string {
    $attributes['type'] = 'text/javascript';
    $attributes['src'] = $this->parseURL($src);
    if ($defer) {
      $attributes['defer'] = 'defer';
    }

    return '<script' . $this->parseAttributes($attributes) . '></script>';
  }

  /**
   * Generate a link tag for a css stylesheet.
   * @param string $href value for the "href" attribute
   * @param array $attributes
   * @return string The link tag.
   */
  public function css(string $href, array $attributes = array()): string {
    $attributes['type'] = 'text/css';
    $attributes['href'] = $this->parseURL($href);
    $attributes['rel'] = 'stylesheet';

    return '<link' . $this->parseAttributes($attributes) . '/>';
  }

  /**
   * Generate an anchor tag.
   * @param string $url value for the "href" attribute.
   * @param string $text inner html for the element.
   * @param string $target value for the "target" attribute.
   * @param array $attributes
   * @return string The generated anchor tag.
   */
  public function a($url, $text = null, $target = null, array $attributes = array()): string {

    $attributes['href'] = $url;

    if ($target !== null) {
      $attributes['target'] = $target;
    }

    if ($text === null) {
      $text = $attributes['href'];
    }

    return '<a' . $this->parseAttributes($attributes) . '>' . html($text) . '</a>';
  }

  /**
   * Generate an img tag.
   * @param string $src
   * @param string $alt
   * @param array $attributes
   * @return string The img tag.
   */
  public function img(string $src, string $alt, array $attributes = array()): string {
    $attributes['src'] = $this->parseURL($src);
    $attributes['alt'] = $alt;

    return '<img' . $this->parseAttributes($attributes) . '/>';
  }

  /**
   * Generate an opening form tag.
   * @param array $attributes
   * @return string The opening form tag.
   */
  public function formOpen(array $attributes = array()): string {
    return '<form' . $this->parseAttributes($attributes) . '>';
  }

  /**
   * Generate an input element.
   * @param string $name
   * @param string $type
   * @param string $value
   * @param array $attributes
   * @return string Generated input element.
   */
  public function input(string $name, string $type = 'text', string $value = '', array $attributes = array()) {
    $attributes['name'] = $name;
    $attributes['type'] = $type;
    $attributes['value'] = $value;

    return '<input' . $this->parseAttributes($attributes) . '/>';
  }

  /**
   * Generate a "select" tag.
   * @param array $options Each element of this array will generate an "option" tag.
   * @param array $attributes
   * @param string $selected The key in $options to mark as selected.
   * @param array $_optAttr Attributes for each option tag.
   * @return string The generated tag.
   */
  public function select(array $options, array $attributes = array(), string $selected = null, array $optionAttributes = []): string {
    $html = '';

    foreach ($options as $key => $value) {
      $_optAttr = $optionAttributes;
      $_optAttr['value'] = html($key);
      
      if ($key == $selected) {
        $_optAttr['selected'] = 'selected';
      }

      $html .= '<option' . $this->parseAttributes($_optAttr) . '>' . html($value) . '</option>';
    }

    return '<select' . $this->parseAttributes($attributes) . '>' . $html . '</select>';
  }

  /**
   * Generate a "ul" tag.
   * @param array $items Each element becomes a "li" tag.
   * @param array $attributes
   * @param array $liattributes Attributes for each li tag.
   * @return string The generated tag.
   */
  public function ul(array $items, array $attributes = array(), array $liattributes = []): string {
    return $this->l('ul', $items, $attributes, $liattributes);
  }

  /**
   * Generate a "оl" tag.
   * @param array $items Each element becomes a "li" tag.
   * @param array $attributes
   * @param array $liattributes Attributes for each li tag.
   * @return string The generated tag.
   */
  public function оl(array $items, array $attributes = array(), array $liattributes = []): string {
    return $this->l('оl', $items, $attributes, $liattributes);
  }

  protected function l(string $type, array $items, array $attributes = [], array $liattributes = []): string {
    $liattr = $this->parseAttributes($liattributes);
    $html = '<' . $type . $this->parseAttributes($attributes) . '>';

    foreach ($items as $li) {
      $html .= '<li' . $liattr . '>' . html($li) . '</li>';
    }

    return $html . '</' . $type . '>';
  }

  /**
   * Parse an associative array into an html tag attributes string, encoding both key and value of the array as html strings.
   * @param array $attributes
   * @return string The parsed attributes string.
   */
  private function parseAttributes(array $attributes): string {
    $string = '';

    foreach ($attributes as $attribute => $value) {
      $string .= ' ' . html($attribute) . '="' . html($value) . '"';
    }

    return $string;
  }

  /**
   * Convert an URI to URL.
   * @param string $uri
   * @return string The URL.
   */
  private function parseURL(string $uri): string {
    return (new URL($uri))->absolute();
  }

  /**
   * Turn an HTML string into one line. Preserves "pre" tags.
   * WARNING: This is experimental and should be used with caution.
   * @param string $html
   * @return string
   */
  public function minify(string $html): string {
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
   * Generates OG, Twitter and other meta tags.
   * 
   * @param array $data Available keys: title, author, keywords, description, image, robots
   * @return string The generated HTML string of meta tags.
   */
  public function SEO(array $data): string {
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
   * @return string The generated HTML string.
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
   * @param string $base The base URL to use for the anchor tags.
   * @param string $route The route which should be used to construct the breadcrumbs. MUST NOT start or end with a forward slash (/)!
   * @param array $attributes Attributes for each breadcrumb anchor tag.
   * @return string HTML output.
   */
  public function breadcrumbs($base, $route, array $attributes = []) {
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

        $crumbs .= $this->a($link, str_replace($search, $replace, $current), null, $attributes);

        $last = $i;
      }
    }

    return $crumbs;
  }

}
