<?php
/**
  * Created by PhpStorm.
  * User: Jan
  * Date: 16.05.2016
  *
  * Session controller
 **/
namespace JSMF;

class Session {

  private static $_started=false;
  private static $_mc=null;
  private static $_sessionHandler=null;
  private static $_injectedSessionId=null;

  private static $_session=[];

  const SESSION_HANDLER_FILES = 'files';
  const SESSION_HANDLER_DATABASE = 'db';
  const SESSION_HANDLER_MEMCACHE = 'memcache';

  /**
   * starts the session
   * @param String|files $sessionHandler
   **/
  public static function init(string $sessionHandler='files') {
    if (self::$_started || headers_sent() || session_id() != '') return;

    if (self::$_injectedSessionId) session_id(self::$_injectedSessionId);

    self::_initializeSessionHandler($sessionHandler);

    // set some security options
    ini_set('session.cookie_domain', $_SERVER['HTTP_HOST']);
    ini_set('session.cookie_httponly', 'On');
    ini_set('session.cookie_secure', 'On');
    ini_set('session.use_strict_mode', 'On');
    ini_set('session.name', 'JSMFSESSION');

    if (Config::has('session.maxlifetime')) {
      ini_set('session.gc_maxlifetime', Config::get('session.maxlifetime'));
    }

    session_start();
    self::$_started=true;
    self::$_session = &$_SESSION;
    if (!isset(self::$_session['__jsmf_session_started'])) self::$_session['__jsmf_session_started'] = time();

    if (self::has('__jsmf_session_destroyed')) {
      $destroyTs = self::get('__jsmf_session_destroyed');
      if ($destroyTs < time()) {
        // self destroying in 3...2...1...!
        foreach (self::$_session as $k => $v) unset(self::$_session[$k]);
        throw new Exception('An expired session was used!');
      }
    }
  }



  /**
   * inject session id
   * @param string $sessionId
   */
  public static function injectSessionId(string $sessionId) {
    self::$_injectedSessionId = $sessionId;
  }


  /**
   * initializes the session handler
   * @param String $sessionHandler
   * @throws \JSMF\Exception
   * @return Void
   **/
  private static function _initializeSessionHandler($sessionHandler) {
    $availableHandlers = ['files', 'memcache', 'db', 'redis'];
    if (!in_array($sessionHandler, $availableHandlers)) throw new Exception('Invalid session Handler type. Possible Handlers: ' . implode(', ', $availableHandlers));

    self::$_sessionHandler = $sessionHandler;

    switch ($sessionHandler) {
      case 'files':
        // default - nothing to do
        break;

      case 'redis':
        ini_set('session.save_handler', 'redis');
        $host=null;
        if (Config::has('session.redis.socket')) {
          $host = 'unix://' . Config::get('session.redis.socket');
        } else if(Config::has('session.redis.host')) {
          $host = 'tcp://' . Config::get('session.redis.host');
        } else {
          throw new Exception('Session handler redis but cache.redis.socket and cache.redis.host not in config');
        }

        $host.='?persistent=1';

        if (Config::has('session.redis.dbindex')) {
          $host.='&database=' . Config::get('session.redis.dbindex');
        }

        ini_set('session.save_path', $host);
        break;
      
      case 'db':
        // check the config
        if (!Config::has('session.db.table')) throw new Exception('You set the session handler to "db" but did not specify "session.db.table" in config which must be the name of the database table to store session data in');
        if (!Config::has('session.db.columns.session_id')) throw new Exception('You set the session handler to "db" but did not specify "session.db.columns.session_id" which must be the column name where to store the session_id in');
        if (!Config::has('session.db.columns.data')) throw new Exception('You set the session handler to "db" but did not specify "session.db.columns.data" which must be the column name where to store the session data in (ensure the column is big enough!)');
        if (!Config::has('session.db.columns.last_update')) throw new Exception('You set the session handler to "db" but did not specify "session.db.columns.last_update" which must be the column name where to store the last update timestamp in');

        if (ini_get('session.gc_probability') == 0) {
          // ensure php session garbage collection is not disabled by session.gc_probability set to 0
          // is done on some systems where session file handling is done by cronjob and PHP handling is disabled - but we need it now!
          ini_set('session.gc_probability', 1);
          ini_set('session.gc_divisor', 100); // probability / divisor = chance that garbage collection runs on php request -> 1 / 100 = 1% chance
        }

        break;

      case 'memcache':
        // check if we have memcache parameters in config
        if (!Config::has('memcache.host')) throw new Exception('You set the session handler to "memcache" but did not specify "memcache.host" in Config!');

        if (!Memcache::connect()) {
          // if we can not connect, fallback to file handling
          self::$_sessionHandler = 'files';
        }
        break;
    }

    if (self::$_sessionHandler != 'files' && self::$_sessionHandler != 'redis') {
      // set custom session handler
      session_set_save_handler(
        array('\\JSMF\\Session', 'sh_open'),
        array('\\JSMF\\Session', 'sh_close'),
        array('\\JSMF\\Session', 'sh_read'),
        array('\\JSMF\\Session', 'sh_write'),
        array('\\JSMF\\Session', 'sh_destroy'),
        array('\\JSMF\\Session', 'sh_gc')
      );
    }
    
    
  }


  /**
   * writes and closes the session
   * @return Void
   */
  public static function close() {
    if (self::$_started) {
      session_write_close();
    }
  }


  /**
   * returns session age in seconds. Useful for exceptio
   * @return int $age
   */
  public static function getSessionAge() :int {
    $startTime=self::get('__jsmf_session_started');
    if ($startTime) {
      return time() - $startTime;
    }
    return 0;
  }


  /**
   * returns if a JSMF Session was started
   * @return boolean $started
   */
  public static function isStarted() :bool {
    return self::$_started;
  }


  /**
   * gets session value or default if not exists
   * @param String $key
   * @param String|null $default
   * @return Mixed
   */
  public static function get(string $key, $default=null) {
    if (isset(self::$_session[$key])) {
      return self::$_session[$key];
    }
    return $default;
  }


  /**
   * Returns all session values
   * @param null $prefix
   * @return array
   */
  public static function getAll($prefix=null) :array {
    if ($prefix === null) return self::$_session;

    $session=[];
    foreach (self::$_session as $k => $v) {
      if (strpos($k, $prefix) === 0) {
        $session[$k] = $v;
      }
    }
    return $session;
  }


  /**
   * sets a session value
   * @param String $key
   * @param String $val
   * @return Void
   */
  public static function set(string $key, $val) {
    self::$_session[$key]=$val;
  }


  /**
   * returns if a session key exists
   * @param String $key
   * @return Boolean
   */
  public static function has(string $key, bool $emptyCheck=false) :bool {
    if ($emptyCheck) return !empty(self::$_session[$key]);

    return isset(self::$_session[$key]);
  }


  /**
   * deletes a sessio key
   * @param String $key
   * @return Void
   */
  public static function delete(string $key) {
    if (isset(self::$_session[$key])) {
      unset(self::$_session[$key]);
    }
  }


  /**
   * regenerates session-id and marks the old session as destroyed 
   * so it will be deleted after short time
   * @return Void
   **/
  public static function regenerateId() {
    // save destroyed timestamp to current "the old" session
    // if some request comes with the old session id, this timestamp is recognized and if the time is reached the old session will destroy itself
    self::set('__jsmf_session_destroyed', time() + 300);

    // regenerate the id
    session_regenerate_id();

    // as session content was copied, remove the destroyed timestamp from "new" session
    self::delete('__jsmf_session_destroyed');
  }


   /**
   * session handler for session open
   * is called from php internal session handler
   * @return void
   **/
  public static function sh_open() {
    return true;
  }


  /**
   * session handler for session close
   * is called from php internal session handler
   * @return void
   **/
  public static function sh_close() {
    // session close - nothing todo as pdo closes on script end
    return true;
  }


  /**
   * session handler for session read
   * is called from php internal session handler
   * @param String $sessionId
   * @return void
   **/
  public static function sh_read($sessionId) {
    switch (self::$_sessionHandler) {
      case self::SESSION_HANDLER_DATABASE:
        $db = Database::getInstance();
        $row = $db->selectOneRow(['data'])
                  ->from(Config::get('session.db.table'))
                  ->where([
                           [ Config::get('session.db.columns.session_id'), '=?' ]
                          ], [ $sessionId ])
                  ->execute();
        return !empty($row['data']) ? $row['data'] : '';
        break;

      case self::SESSION_HANDLER_MEMCACHE:
        if (Memcache::has(md5($_SERVER['HTTP_HOST']) . '_session_' . $sessionId)) {
          return Memcache::get(md5($_SERVER['HTTP_HOST']) . '_session_' . $sessionId, '');
        }
        return false;
        break;
    }
  }


  /**
   * session handler for session write
   * is called from php internal session handler
   * @param String $sessionId
   * @param String $data
   * @return void
   **/
  public static function sh_write($sessionId, $data) {
    switch (self::$_sessionHandler) {
      case self::SESSION_HANDLER_DATABASE:
        $db = Database::getInstance();
        return $db->insertInto(Config::get('session.db.table'), null, false, true)
                  ->values([
                    Config::get('session.db.columns.session_id') => $sessionId,
                    Config::get('session.db.columns.data') => $data,
                    Config::get('session.db.columns.last_update') => date('Y-m-d H:i:s'),
                  ])
                  ->execute();
        break;

      case self::SESSION_HANDLER_MEMCACHE:
        $expire = ini_get('session.gc_maxlifetime');
        if (empty($expire)) $expire = 1440;
    
        return Memcache::set(md5($_SERVER['HTTP_HOST']) . '_session_' . $sessionId, $data, $expire);
        break;
    }
  }


  /**
   * session handler for session destroy
   * is called from php internal session handler
   * @param String $sessionId
   * @return void
   **/
  public static function sh_destroy($sessionId) {
    switch (self::$_sessionHandler) {
      case self::SESSION_HANDLER_DATABASE:
        $db = Database::getInstance();
        return $db->deleteFrom(Config::get('session.db.table'))
                  ->where([
                            [ Config::get('session.db.columns.session_id') , '=?' ]
                          ], [ $sessionId ]
                    )
                  ->execute();
        break;

      case self::SESSION_HANDLER_MEMCACHE:
        return Memcache::delete(md5($_SERVER['HTTP_HOST']) . '_session_' . $sessionId);
        break;
    }
  }


  /**
   * session handler for session garbage collection
   * is called from php internal session handler
   * @param int $lifetime
   * @return void
   **/
  public static function sh_gc($lifetime) {
    if (empty($lifetime)) return true;

    switch (self::$_sessionHandler) {
      case self::SESSION_HANDLER_DATABASE:
        $db = Database::getInstance();
        $db->query('DELETE FROM `' . Config::get('session.db.table') . '` WHERE ADDDATE(last_update, INTERVAL ' . (int)$lifetime.' SECOND) < NOW()');
        return true;
        break;

      case self::SESSION_HANDLER_MEMCACHE:
        // do no garbage collection, memcache item will expire
        break;
    }
  }
}
