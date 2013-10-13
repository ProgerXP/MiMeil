<?php

class LaMeil extends MiMeil {
  static $eventPrefix = 'mimeil: ';

  //= null, Laravel\View
  public $view;

  // Configures MiMeil (its event handler) so it becomes usable.
  static function initMiMeil() {
    $prefix = static::$eventPrefix;

    static::$onEvent = function ($event, $args) use ($prefix) {
      return Event::until($prefix.$event, $args);
    };

    static::registerEventsUsing(array(get_called_class(), 'listen'));

    // saves outgoing messages in a local folder if enabled in the config.
    static::listen('transmit', function (&$subject, &$headers, &$body, $mail) {
      if ($path = $mail->echoPath) {
        $mail->saveEML($subject, $headers, $body, rtrim($path, '\\/').'/');
      }
    });
  }

  // Makes $callback being called on MiMeil's event named $event (see MiMeil->fire()).
  static function listen($event, $callback) {
    Event::listen($event = static::$eventPrefix.$event, $callback);
    return $event;
  }

  // Composes mail's body from a view and dispatches it to $recipient. The view
  // will receive additional $vars (see ::compose()).
  //
  //= MiMeil    on successful transmission
  //= null      on error
  static function sendTo($recipients, $view, array $vars = array()) {
    $mail = static::compose($view, $vars);

    if (!$mail->subject) {
      is_object($view) and $view = $view->view;
      throw new Exception("No message subject set by the e-mail template [$view].");
    }

    $mail->to = is_array($recipients) ? $recipients : (array) $recipients;
    $func = $mail->config('simulate') ? 'simulateSending' : 'send';

    if ($mail->$func()) {
      return $mail;
    } else {
      Log::warn("MiMeil: cannot send e-mail message to ".join(', ', $mail->to).".");
    }
  }

  // Creates a blank message with HTML body set to rendered $view.
  //
  //= LaMeil
  static function compose($view, array $vars = array()) {
    is_object($view) or $view = View::make($view);

    return static::make()
      ->initView( $view->with($vars) )
      ->body('html', trim($view->render()));
  }

  // Creates a blank message.
  //
  //= LaMeil
  static function make($to = '', $subject = '') {
    return new static($to, $subject);
  }

  // Overriden MiMeil's MIME detector - using Laravel's native facility.
  //
  //= string
  function MimeByExt($ext, $default = true) {
    $default = $default ? static::$defaultMIME : $ext;
    return File::mime($ext, $default);
  }

  // Applies default settings from config/mail.php.
  // Is called by MiMeil->__construct().
  protected function init() {
    foreach ((array) $this->config() as $prop => $value) {
      $this->$prop = $value;
    }
  }

  function config($key = null) {
    return $key ? Config::get("mimeil.$key") : Config::get('mimeil');
  }

  // Sets initial view variables regarding message composition to $view. These are
  // accessible to all message templates being rendered.
  function initView(Laravel\View $view) {
    $this->view = $view;

    $view->data += array(
      'mail'              => $this,
      'styles'            => array(),
      'header'            => array(),
      'footer'            => array(),
    );

    $this->defaultViewDataTo($view->data);
    return $this;
  }

  // To be overriden in child classes.
  //
  // Can be used to add initial variables passed to a View being composed (see
  // ::compose() and ->initView()).
  //
  //= null      return value is ignored
  protected function defaultViewDataTo(array &$data) { }

  // Attaches a file from local $path with $name visible to the recipient.
  function attachLocal($path, $name, $options = array()) {
    if (!is_file($path)) {
      Log::error("MiMeil: attachment file [$path] doesn't exist - ignoring.");
    } else {
      is_array($options) or $options = array('mime' => $options);

      $options += array(
        'name'            => $name,
        'mime'            => null,
        'headers'         => array(),
        'related'         => false,
      );

      $this->attach($name, file_get_contents($path), $options['mime'],
                    $options['headers'], $options['related']);
    }

    return $this;
  }

  // Similar to attachLocal() but marks the file as "related" - used in HTML
  // decoration and invisible in the attachment list of the mail agent. Unlike
  // normal related attachments can be referred to with "cid:NAME".
  //
  //? attachRelatedLocal('/tmp/php3DC.tmp', 'a-pic.png');
  //    // refer to this file in message's HTML body as <img src="cid:a-pic.png">
  //
  function attachRelatedLocal($path, $name, $options = array()) {
    is_array($options) or $options = array('mime' => $options);
    $options += array('related' => true);
    return $this->attachLocal($path, $name, $options);
  }

  // Attaches a stylesheet to this message. If $path is omitted uses locates .css
  // with the same name and directory as the main message template. If $path is
  // given it can be of form [bndl::]path[.file[...]] - relative to bndl's views/
  // or application/views/ if 'bndl::' is omitted or only '::' is present.
  //
  // If stylesheet file cannot be found logs a warning and does nothing.
  function styleLocal($path = null) {
    if ($view = $this->reqView()) {
      if (!$path) {
        $path = S::newExt($view->path, '.css');
      } elseif (strpos($path, '::') !== false) {
        list($bundle, $path) = explode('::', $path, 2);
        $path = Bundle::path($bundle).'views'.DS.str_replace('.', DS, $path).'.css';
      }

      if (is_file($path)) {
        $view->data['styles'][] = file_get_contents($path);
      } else {
        Log::warn("MiMeil: stylesheet file [$path] doesn't exist - ignoring.");
      }
    }

    return $this;
  }

  //= Laravel\View
  //= null      if unassigned (logs a warning)
  function reqView() {
    if ($this->view) {
      return $this->view;
    } else {
      Log::warn("MiMeil: e-mail message has no associated template (\$this->view).");
    }
  }

  // Sets message subject both of this object and its associated view, if any.
  function subject($str) {
    $this->subject = $str;
    // Reflect the change in assigned view's variables.
    $this->view and $this->view->subject = $this->subject;
    return $this;
  }
}

LaMeil::initMiMeil();