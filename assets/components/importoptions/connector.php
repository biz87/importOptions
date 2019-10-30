<?php
if (file_exists(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php')) {
    /** @noinspection PhpIncludeInspection */
    require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
} else {
    require_once dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/config.core.php';
}
/** @noinspection PhpIncludeInspection */
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
/** @noinspection PhpIncludeInspection */
require_once MODX_CONNECTORS_PATH . 'index.php';
/** @var importOptions $importOptions */
$importOptions = $modx->getService('importOptions', 'importOptions', MODX_CORE_PATH . 'components/importoptions/model/');
$modx->lexicon->load('importoptions:default');

// handle request
$corePath = $modx->getOption('importoptions_core_path', null, $modx->getOption('core_path') . 'components/importoptions/');
$path = $modx->getOption('processorsPath', $importOptions->config, $corePath . 'processors/');
$modx->getRequest();

/** @var modConnectorRequest $request */
$request = $modx->request;
$request->handleRequest([
    'processors_path' => $path,
    'location' => '',
]);