<?php

class AppPhpmemadmin extends Module
{
    const ROOT_CFG_VERSION = 'phpmemadminVersion';
    
    const LOCAL_CFG_CONF = 'phpmemadminConf';
    
    private $conf;

    public function __construct($id, $type) {
        Util::logInitClass($this);
        $this->reload($id, $type);
    }

    public function reload($id = null, $type = null) {
        global $neardConfig, $neardLang;

        $this->name = $neardLang->getValue(Lang::PHPMEMADMIN);
        $this->version = $neardConfig->getRaw(self::ROOT_CFG_VERSION);
        parent::reload($id, $type);

        if ($this->neardConfRaw !== false) {
            $this->conf = $this->currentPath . '/' . $this->neardConfRaw[self::LOCAL_CFG_CONF];
        }
        
        if (!$this->enable) {
            Util::logInfo($this->name . ' is not enabled!');
            return;
        }
        if (!is_dir($this->currentPath)) {
            Util::logError(sprintf($neardLang->getValue(Lang::ERROR_FILE_NOT_FOUND), $this->name . ' ' . $this->version, $this->currentPath));
        }
        if (!is_file($this->neardConf)) {
            Util::logError(sprintf($neardLang->getValue(Lang::ERROR_CONF_NOT_FOUND), $this->name . ' ' . $this->version, $this->neardConf));
        }
        if (!is_file($this->conf)) {
            Util::logError(sprintf($neardLang->getValue(Lang::ERROR_CONF_NOT_FOUND), $this->name . ' ' . $this->version, $this->conf));
        }
    }

    protected function updateConfig($version = null, $sub = 0, $showWindow = false) {
        global $neardBs, $neardBins;
        
        if (!$this->enable) {
            return true;
        }
        
        $version = $version == null ? $this->version : $version;
        Util::logDebug(($sub > 0 ? str_repeat(' ', 2 * $sub) : '') . 'Update ' . $this->name . ' ' . $version . ' config...');
    
        $alias = $neardBs->getAliasPath() . '/phpmemadmin.conf';
        if (is_file($alias)) {
            Util::replaceInFile($alias, array(
                '/^Alias\s\/phpmemadmin\s.*/' => 'Alias /phpmemadmin "' . $this->getCurrentPath() . '/web/"',
                '/^<Directory\s.*/' => '<Directory "' . $this->getCurrentPath() . '/web/">',
            ));
        } else {
            Util::logError($this->getName() . ' alias not found : ' . $alias);
        }
        
        Util::replaceInFile($this->getConf(), array(
            '/^\s\s\s\s\s\s\s\s"port"/' => '        "port": ' . $neardBins->getMemcached()->getPort(),
        ));
    }
    
    public function setVersion($version) {
        global $neardConfig;
        $this->version = $version;
        $neardConfig->replace(self::ROOT_CFG_VERSION, $version);
    }

    public function getConf() {
        return $this->conf;
    }
}
