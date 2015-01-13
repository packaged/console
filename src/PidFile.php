<?php
/**
 * @author  Richard.Gooding
 */

namespace Packaged\Console;

class PidFile
{
  private $_pidFilePath;

  public function __construct($path)
  {
    $this->_pidFilePath = $path;
    $this->_createPidFile();
  }

  public function __destruct()
  {
    $this->_deletePidFile();
  }

  private function _createPidFile()
  {
    $argv = $_SERVER['argv'];

    if(file_exists($this->_pidFilePath))
    {
      $oldpid = trim(file_get_contents($this->_pidFilePath));
      if(file_exists('/proc/' . $oldpid))
      {
        $cmdLine = explode(
          chr(0),
          file_get_contents('/proc/' . $oldpid . '/cmdline')
        );

        // strip off a leading "php" or "cubex" command
        $baseCmd = strtolower(trim(basename($argv[0])));
        if(($baseCmd == "php") || ($baseCmd == "cubex"))
        {
          $thisCmd = $argv[1];
        }
        else
        {
          $thisCmd = $argv[0];
        }

        // Search for this script in the existing process's command line
        // (not particularly scientific!)
        if(in_array($thisCmd, $cmdLine))
        {
          throw new \Exception(
            'Another instance is already running, PID ' . $oldpid
          );
        }
      }
      unlink($this->_pidFilePath);
    }
    else
    {
      $pidDir = dirname($this->_pidFilePath);
      if(!file_exists($pidDir))
      {
        if(!mkdir($pidDir, 0755, true))
        {
          throw new \Exception('Error creating PID file directory ' . $pidDir);
        }
      }
    }

    file_put_contents($this->_pidFilePath, getmypid());
    if(!file_exists($this->_pidFilePath))
    {
      throw new \Exception('Failed to create PID file');
    }
  }

  private function _deletePidFile()
  {
    if(file_exists($this->_pidFilePath))
    {
      unlink($this->_pidFilePath);
    }
  }
}
