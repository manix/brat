<?php

namespace Manix\Brat\Utility\Errors;

use Manix\Brat\Utility\BootstrapLayout;
use function html;

class DebugErrorView extends BootstrapLayout {

  public $title = 'Error.';

  public function body() {
    $t = $this->data;
    ?>

    <div class="heading bg-danger text-white h2 mb-0 d-flex align-items-center justify-content-between" style="min-height: 20vh; padding: 0 5%;">
      <div>
        <i class="fa fa-lg fa-exclamation-triangle mr-3"></i>
        <?= DEBUG_MODE ? html($t->getMessage()) : $this->getHTTPMsg($t->getCode()) ?>
      </div>
      <div>
        <?= $t->getCode() ?>
      </div>
    </div>
    <?php if (DEBUG_MODE): ?>
      <div class="table-responsive">
        <table class="table table-hover">
          <?php foreach ($t->getTrace() as $index => $record): ?>
            <tr>
              <td class="p-4">#<?= $index ?></td>
              <td class="p-4"><?= $record['file'], ':', $record['line'] ?></td>
              <td class="p-4">
                <?=
                html($this->formatClassName($record['class'] ?? null) . ($record['type'] ?? null) . $record['function']),
                '(', implode(', ', $this->parseArgs($record['args'])), ')'
                ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
      <script>
        window.onload = function () {
          $('[data-toggle="popover"]').click(e => {
            e.stopPropagation();
            e.preventDefault();
          }).popover();
        };
      </script>
    <?php else: ?>
      <div class="d-flex justify-content-center align-items-center" style="font-size: 300%; height: 80vh;">
        <i class="fa fa-5x fa-fire-extinguisher" style="color: #f1f1f1"></i>
      </div>
    <?php endif; ?>

    <?php
  }

  protected function formatClassName($name) {
    return strpos($name, 'class@anonymous') === 0 ? 'class@anonymous' : $name;
  }

  protected function parseArgs(array $args) {
    $parsed = [];

    foreach ($args as $arg) {
      if (is_scalar($arg) || $arg === null) {
        $parsed[] = html(var_export($arg, true));
      } else {
        $type = gettype($arg);

        if ($type === 'object') {
          $type = $this->formatClassName(get_class($arg));
        } else {
          $type = ucfirst($type);
        }

        $parsed[] = $this->html->a('#', $type, null, [
            'data-container' => 'body',
            'data-toggle' => 'popover',
            'data-placement' => 'bottom',
            'data-trigger' => 'focus',
            'data-html' => 'true',
            'data-content' => $this->html->pre(print_r($arg, true))
        ]);
      }
    }

    return $parsed;
  }

  public function getHTTPMsg($code) {
    return array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        449 => 'Retry With',
        450 => 'Blocked by Windows Parental Controls',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        509 => 'Bandwidth Limit Exceeded',
        510 => 'Not Extended'
    )[$code] ?? 'Internal Server Error';
  }

}
