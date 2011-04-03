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

    $url = "http://xbox/xbmcCmds/xbmcHttp?command=".urlencode($command);
    curl_setopt($this->curl, CURLOPT_URL, $url);
    $result = curl_exec($this->curl);
    if ($result === false)
    {
      $this->isRunning = false;
      //throw new Exception(curl_error($this->curl) . ' ' . $url);
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

  /*
  // http://xbox/xbmcCmds/xbmcHttp?command=Mute()
  public function mute()
  {
    $result = $this->sendCommand('Mute()');
  }
   */
  
  protected function getLog($filename)
  {
    if ($this->isRunning === false)
      return false;

    return file_get_contents('ftp://xbox:xbox@xbox/E/Apps/XBMC/' . $filename);
  }

  public function getCurrentLog()
  {
    return $this->getLog('xbmc.log');
  }

  public function getOldLog()
  {
    return $this->getLog('xbmc.old.log');
  }

  public function __call($name, $arguments) 
  {
    $command = $name . '(';

    $first = true;
    foreach ($arguments as $argument)
    {
      if ($first)
        $first = false;
      else
        $command .= ';';

      if ($argument == '')
      {
        // To pass an empty parameter insert a space between the semi-colons "; ;". 
        $argument = ' ';
      }
      else
      {
        // To pass a semi-colon as a parameter (rather than a separator) use two semi-colons ";;". 
        $argument = str_replace(';', ';;', $argument);
      }

      $command .= $argument;
    }

    $command .= ')';

    //var_dump($command);
    $result = $this->sendCommand($command);
    if ($result === false)
      return false;

    $result = self::splitXbmcResult();
    var_dump($result);
    array_shift($result); // first element is empty
    array_pop($result);   // last element is empty
    if (count($result) == 0)
      return null;
    else if (count($result) == 1)
      return $result[0];
    else
      return $result;
  }

}


