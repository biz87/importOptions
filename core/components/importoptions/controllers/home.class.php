<?php

/**
 * The home manager controller for importOptions.
 *
 */
class importOptionsHomeManagerController extends modExtraManagerController
{
    /** @var importOptions $importOptions */
    public $importOptions;


    /**
     *
     */
    public function initialize()
    {
        $this->importOptions = $this->modx->getService('importOptions', 'importOptions', MODX_CORE_PATH . 'components/importoptions/');
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['importoptions:default'];
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('importoptions');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->importOptions->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->importOptions->config['jsUrl'] . 'mgr/importoptions.js');
        $this->addJavascript($this->importOptions->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->importOptions->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->importOptions->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->importOptions->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addHtml('<script type="text/javascript">
        importOptions.config = ' . json_encode($this->importOptions->config) . ';
        importOptions.config.connector_url = "' . $this->importOptions->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "importoptions-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="importoptions-panel-home-div"></div>';

        return '';
    }
}