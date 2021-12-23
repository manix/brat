<?php

namespace Manix\Brat\Components\Errors;

use Manix\Brat\Utility\BootstrapLayout;
use Throwable;
use const DEBUG_MODE;
use function html;

class View extends BootstrapLayout {

  public $title = 'Error.';

  public function body() {

    $t = $this->data['throwable'];
    ?>

    <div class="heading bg-danger text-white h2 mb-0 d-flex align-items-center justify-content-between" style="min-height: 20vh; padding: 0 5%;">
      <div>
        <i class="fa fa-lg fa-exclamation-triangle mr-3"></i>
        <?= DEBUG_MODE ? html($t->getMessage()) : $this->getHTTPMsg($t) ?>
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
                '(', implode(', ', $this->parseArgs($record['args'] ?? [])), ')'
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

  public function getHTTPMsg(Throwable $throwable) {
    switch ($throwable->getCode()) {
      case 100: return 'Continue';
      case 101: return 'Switching Protocols';
      case 102: return 'Processing';
      case 200: return 'OK';
      case 201: return 'Created';
      case 202: return 'Accepted';
      case 203: return 'Non-Authoritative Information';
      case 204: return 'No Content';
      case 205: return 'Reset Content';
      case 206: return 'Partial Content';
      case 207: return 'Multi-Status';
      case 300: return 'Multiple Choices';
      case 301: return 'Moved Permanently';
      case 302: return 'Found';
      case 303: return 'See Other';
      case 304: return 'Not Modified';
      case 305: return 'Use Proxy';
      case 306: return 'Switch Proxy';
      case 307: return 'Temporary Redirect';
      case 400: return 'Bad Request';
      case 401: return 'Unauthorized';
      case 402: return 'Payment Required';
      case 403: return 'Forbidden';
      case 404: return 'Not Found';
      case 405: return 'Method Not Allowed';
      case 406: return 'Not Acceptable';
      case 407: return 'Proxy Authentication Required';
      case 408: return 'Request Timeout';
      case 409: return 'Conflict';
      case 410: return 'Gone';
      case 411: return 'Length Required';
      case 412: return 'Precondition Failed';
      case 413: return 'Request Entity Too Large';
      case 414: return 'Request-URI Too Long';
      case 415: return 'Unsupported Media Type';
      case 416: return 'Requested Range Not Satisfiable';
      case 417: return 'Expectation Failed';
      case 418: return 'I\'m a teapot';
      case 422: return 'Unprocessable Entity';
      case 423: return 'Locked';
      case 424: return 'Failed Dependency';
      case 425: return 'Unordered Collection';
      case 426: return 'Upgrade Required';
      case 449: return 'Retry With';
      case 450: return 'Blocked by Windows Parental Controls';
      case 500: return 'Internal Server Error';
      case 501: return 'Not Implemented';
      case 502: return 'Bad Gateway';
      case 503: return 'Service Unavailable';
      case 504: return 'Gateway Timeout';
      case 505: return 'HTTP Version Not Supported';
      case 506: return 'Variant Also Negotiates';
      case 507: return 'Insufficient Storage';
      case 509: return 'Bandwidth Limit Exceeded';
      case 510: return 'Not Extended';
      case Exception::DISPLAY_CODE: return $throwable->getMessage();
      default: return 'Internal Server Error';
    }
  }

}
