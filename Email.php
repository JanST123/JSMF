<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Email class
 **/

namespace JSMF;


class Email {
  public static $fromEmail=null;
  public static $fromName=null;
  public static $replyTo=null;
  public static $charset='utf-8';

  private static function _init() {
    if (empty(self::$fromEmail)) self::$fromEmail = 'noreply@' . (empty($_SERVER['HTTP_HOST']) ? 'localhost' : $_SERVER['HTTP_HOST']);
    if (empty(self::$fromName)) self::$fromName = self::$fromEmail;
    if (empty(self::$replyTo)) self::$replyTo = self::$fromEmail;

  }

  public static function send(string $to, string $subject, string $body, array $additionalHeaders=[]) :bool {
    self::_init();

    // detect html
    $isHtml=false;
    if (preg_match('/<[a-z0-9]+\s?\/?>.*(<\/[a-z0-9]+>)?/', $body)) $isHtml=true;

    if ($isHtml) {
      $boundary   =rand(0,9)."-"
                  .rand(10000000000,9999999999)."-"
                  .rand(10000000000,9999999999)."=:"
                  .rand(10000,99999);
    }

    $headers=array_merge(
      [
        'FROM: ' . '=?' . self::$charset . '?B?'.base64_encode(self::$fromName).'?=' . ' <' . self::$fromEmail . '>',
        'Reply-To: ' . self::$replyTo,
        'Content-Type: ' . ($isHtml ? 'multipart/alternative;boundary='.$boundary : 'text/plain; charset=' . self::$charset),
        'Content-Transfer-Encoding: quoted-printable',
        'MIME-Version: 1.0',
        'X-Mailer: JSMF PHP Framework',
      ],
      $additionalHeaders
    );

    if ($isHtml) {
      $tmp = imap_8bit(nl2br($body));
      $body = "This is a MIME encoded message.";
      $body.="\r\n\r\n--" . $boundary ."\r\nContent-Type: text/plain; charset=" . self::$charset . "\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n" . strip_tags($tmp);
      $body.="\r\n\r\n--" . $boundary ."\r\nContent-Type: text/html; charset=" . self::$charset . "\r\nContent-Transfer-Encoding: quoted-printable\r\n\r\n" . $tmp;
      $body.="\r\n\r\n--" . $boundary ."--";
    }


    $success = mail(
      $to,
      '=?' . self::$charset . '?B?'.base64_encode($subject).'?=',
      $body,
      implode("\r\n", $headers)
    );



    return $success;
  }

}