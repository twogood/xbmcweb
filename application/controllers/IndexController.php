<?php

class IndexController extends Zend_Controller_Action
{
  protected $xbmc;

  public function init()
  {
    $this->_redirector = $this->_helper->getHelper('Redirector');

    $this->xbmc = new Default_Model_Xbmc();

    $this->view->screenshotWidth = 300;
    $this->view->screenshotHeight = 200;
  }

  public function pauseAction()
  {
    $currentlyPlaying = $this->xbmc->getCurrentlyPlaying();
    if ($currentlyPlaying['PlayStatus'] == 'Playing')
      $this->xbmc->sendCommand('pause()');

    $this->_redirector->gotoRoute(
      array(
        'action' => 'index',
      )
    );

  }

  public function resumeAction()
  {
    $currentlyPlaying = $this->xbmc->getCurrentlyPlaying();
    if ($currentlyPlaying['PlayStatus'] == 'Paused')
      $this->xbmc->sendCommand('pause()');

    $this->_redirector->gotoRoute(
      array(
        'action' => 'index',
      )
    );

  }

  public function indexAction()
  {
    if (!$this->xbmc->isRunning())
    {
      $this->render('not-running');
      return;
    }

    $this->view->currentlyPlaying = $this->xbmc->getCurrentlyPlaying();
  }

  public function screenshotAction()
  {
    $this->_helper->viewRenderer->setNoRender(true);

    if (!$this->xbmc->isRunning())
    {
      $this->getResponse()->setHttpResponseCode(404);
      return;
    } 

    $width = $this->_getParam('w');
    $height = $this->_getParam('h');
    if (!$width)
      $width = $this->view->screenshotWidth;
    if (!$height)
      $height = $this->view->screenshotHeight;

    $result = implode(explode(']', $this->xbmc->sendCommand(
      'takescreenshot(;false;0;'.$width.';'.$height.';90;true)')));
    $this->_helper->layout->disableLayout();
    $this->getResponse()
      ->setHeader('Content-Type', 'image/jpeg; charset=binary')
      ->appendBody(base64_decode($result));
  }

  public function notificationAction()
  {
    $header = $this->_getParam('header');
    $message = $this->_getParam('message');
    $time = '';
    $this->xbmc->notification($header, $message, $time);
    $this->_redirector->gotoRoute(
      array(
        'action' => 'index',
      )
    );
  }

  public function stopAction()
  {
    $this->xbmc->sendCommand('stop()');
    $this->_redirector->gotoRoute(
      array(
        'action' => 'index',
      )
    );
  }

  public function shutdownAction()
  {
    $this->xbmc->sendCommand('shutdown()');
    $this->_redirector->gotoRoute(
      array(
        'action' => 'index',
      )
    );
  }

  public function muteAction()
  {
    $this->xbmc->mute();
    $this->_redirector->gotoRoute(
      array(
        'action' => 'index',
      )
    );
  }

  public function playAction()
  {
    $path = $this->_getParam('path');
    $this->xbmc->sendCommand("PlayFile($path)");
    $this->_redirector->gotoRoute(
      array(
        'action' => 'index',
      )
    );
  }

}

