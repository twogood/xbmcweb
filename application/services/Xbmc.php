<?php

class Default_Service_Xbmc
{
  private $isRunning = true;

  public function __construct()
  {
    $this->curl = curl_init();
    curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT_MS, 1000);
    curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);

    $this->isRunning = $this->sendCommand(
      "setresponseformat(webheader;false;webfooter;false;header;];footer;[;opentag;[;closetag;];closefinaltag;true)")
      !== false;
  }

  public function __destruct()
  {
    curl_close($this->curl);
  }

  public function isRunning()
  {
    return $this->isRunning;
  }

  public function sendCommand($command)
  {
    if ($this->isRunning === false)
      return false;

    curl_setopt($this->curl, CURLOPT_URL, "http://xbox/xbmcCmds/xbmcHttp?command=".urlencode($command));
    $result = curl_exec($this->curl);
    if ($result === false)
    {
      $this->isRunning = false;
    }
    return $result;
  }

  public static function splitXbmcResult($result)
  {
    return explode('][', $result);
  }

  public function getCurrentlyPlaying()
  {
    $result = $this->sendCommand('GetCurrentlyPlaying');

    if (strpos($result, 'Nothing Playing') !== false)
    {
      return array(
        'PlayStatus' => 'Stopped',
      );
    }

    $currentlyPlaying = array();

    if ($result !== false)
    {
      $result = self::splitXbmcResult($result);
      foreach ($result as $pair)
      {
        if ($pair)
        {
          $pair = explode(':', $pair, 2);
          $currentlyPlaying[$pair[0]] = $pair[1];
        }
      }
    }
    return $currentlyPlaying;
  }

  public function notification($header, $message, $time = '', $image = '')
  {
    return $this->sendCommand("ExecBuiltIn(Notification($header,$message,$time,$image))");
  }

}


