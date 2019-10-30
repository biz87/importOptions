<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class importOptions
{
    /** @var modX $modx */
    public $modx;

    /** @var \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet */
    public $spreadsheet;

    public $parents = array();

    public $languageKeys;

    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/importoptions/';
        $assetsUrl = MODX_ASSETS_URL . 'components/importoptions/';

        $this->config = array_merge([
            'corePath' => $corePath,
            'processorsPath' => $corePath . 'processors/',
            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
        ], $config);
        $this->modx->lexicon->load('importoptions:default');
        $this->parents = $this->getParents();
        $this->languageKeys = $this->getLanguageKeys();
    }

    public function getSheetCount($sheet)
    {
        $this->setSpreadsheet($sheet);
        $sheetCount = $this->spreadsheet->getSheetCount();

        return $sheetCount;
    }

    public function getSheetDataByIndex($pIndex)
    {
        $sheet = $this->spreadsheet->getSheet($pIndex);
        $sheetData = $sheet->toArray(null, true, true, true);
        $sheetData = $this->cleanSpreadsheetData($sheetData);
        $sheetData = $this->changeKeys($sheetData);
        $tableData['data'] = $sheetData;
        $tableData['title'] = $sheet->getTitle();
        return $tableData;
    }

    public function getSheet($sheet)
    {
        $this->setSpreadsheet($sheet);
        $sheet = $this->spreadsheet->getSheet(0);
        $sheetData = $sheet->toArray(null, true, true, true);
        $sheetData = $this->cleanSpreadsheetRow($sheetData);
        $sheetData = $this->changeKeys($sheetData);
        return $sheetData;
    }

    private function setSpreadsheet($sheet)
    {
        $spreadsheet = IOFactory::load($sheet);
        $this->spreadsheet = $spreadsheet;
    }

    private function cleanSpreadsheetRow($sheetData)
    {
        foreach ($sheetData as $k => $sheetRow) {
            if (empty($sheetRow['A']) && empty($sheetRow['B']) && empty($sheetRow['C'])) {
                unset($sheetData[$k]);
            }
        }

        return $sheetData;
    }

    private function changeKeys($sheetData)
    {
        $keys = array();
        $countKeys = 0;
        $changedSheetData = array();

        foreach ($sheetData as $k => $sheetRow) {
            if ($sheetRow['A'] == 'COLLECTION') {
                foreach ($sheetRow as $sheetValue) {
                    if (!empty($sheetValue)) {
                        $keys[] = $this->normalizeKey($sheetValue);
                    }
                }
                $countKeys = count($keys);
                unset($sheetData[$k]);
                break;
            }
        }

        foreach ($sheetData as $k => $sheetRow) {
            $i = 1;
            foreach ($sheetRow as $key => $value) {
                if ($i > $countKeys) {
                    unset($sheetRow[$key]);
                }
                $i++;
            }
            $values = array_values($sheetRow);
            $sheetRow = array_combine($keys, $values);

            $sheetData[$k] = $sheetRow;
        }

        return $sheetData;
    }

    private function normalizeKey($k)
    {
        $old_k = trim($k);
        $tmp = explode('(', $k);
        $k = trim($tmp[0]);

        $optionsKeys = array(
            'COLLECTION' => 'grant_name',
            'SUB-COLLECTION' => 'parent_name',
            'REFERENCE' => 'pagetitle',
            'WATCH WEIGHT' => 'weight',
            'PRICE USD' => 'price',
            'MOVEMENT TYPE' => 'MOVEMENT TYPE',
            'EAN' => 'EAN',
            'MOVEMENT CALIBER' => 'MOVEMENT CALIBER',
            'MOVEMENT ADDITIONAL INFORMATION' => 'MOVEMENT ADDITIONAL INFORMATION',
            'MOVEMENT ADDITIONAL INFORMATION 2' => 'MOVEMENT ADDITIONAL INFORMATION 2',
            'FUNCTION WATCH FINDER' => 'FUNCTION WATCH FINDER',
            'FUNCTION WATCH FINDER ADDITIONAL' => 'FUNCTION WATCH FINDER ADDITIONAL',
            'FUNCTIONS LINE 1' => 'FUNCTIONS LINE 1',
            'FUNCTIONS LINE 2' => 'FUNCTIONS LINE 2',
            'FUNCTIONS LINE 3' => 'FUNCTIONS LINE 3',
            'FUNCTIONS LINE 4' => 'FUNCTIONS LINE 4',
            'WATER RESISTANCE' => 'WATER RESISTANCE',
            'CASE MATERIAL' => 'CASE MATERIAL',
            'MATERIAL' => 'MATERIAL',
            'CASE DIAMETER' => 'CASE DIAMETER',
            'CASE HEIGHT' => 'CASE HEIGHT',
            'CRYSTAL' => 'CRYSTAL',
            'BRACELET COLOUR' => 'BRACELET COLOUR',
            'BRACELET MATERIAL' => 'BRACELET MATERIAL',
            'BRACELET DIMENSIONS' => 'BRACELET DIMENSIONS',
            'SUB-COLLECTION RANK' => 'SUB COLLECTION RANK',
            'REFERENCE RANK' => 'REFERENCE RANK',
            'DIAL COLOUR' => 'DIAL COLOUR',
            'CROWN' => 'CROWN',
        );

        if ($k == 'FUNCTION') {
            if ($old_k == 'FUNCTION (FOR WATCH FINDER)') {
                $k = 'FUNCTION WATCH FINDER';
                return $k;
            }

            if ($old_k == 'FUNCTION (FOR WATCH FINDER) ADDITIONAL') {
                $k = 'FUNCTION WATCH FINDER ADDITIONAL';
                return $k;
            }
        }

        if (array_key_exists($k, $optionsKeys)) {
            return $optionsKeys[$k];
        } else {
            return '!' . $k;
        }


    }

    public function isExistsProduct($pagetitle)
    {
        $product = $this->modx->getObject('msProduct', array(
            'class_key' => 'msProduct',
            'pagetitle' => $pagetitle
        ));

        return $product;
    }

    public function getParent($productData)
    {
        $parents = $this->parents;
        $findParent = array_search($productData['parent_name'], $parents);
        if ($findParent) {
            $productData['parent'] = $findParent;
        }

        return $productData;
    }

    private function getParents()
    {
        $output = array();
        $q = $this->modx->newQuery('msCategory');
        $q->where(array(
            'class_key' => 'msCategory'
        ));
        $q->select('id,pagetitle');
        $parents = $this->modx->getIterator('msCategory', $q);
        if ($parents) {
            foreach ($parents as $parent) {
                $output[$parent->id] = $parent->pagetitle;
            }
        }

        return $output;
    }

    public function updateProduct($productData, $product)
    {
        $processorData = array(
            'id' => $product->id,
            'context_key' => 'web',
            'parent' => $product->parent,
            'class_key' => 'msProduct',
            'pagetitle' => $productData['pagetitle'],
            'price' => $productData['price'],
            'weight' => $productData['weight'],
        );

        if (isset($productData['parent']) && $productData['parent'] !== $product->parent) {
            $processorData['parent'] = $productData['parent'];
        }

        $response = $this->modx->runProcessor('resource/update', $processorData);


        if ($response->isError()) {
            $this->modx->log(1, print_r($response->getMessage(), 1));
        }

        $this->createLocalizatorContent($productData);


    }

    /**
     * @param $productData
     * @param $lang_key
     */
    public function updateOptions($productData)
    {
        $product_id = $productData['id'];
        //update options
        unset($productData['id']);
        unset($productData['pagetitle']);
        unset($productData['parent']);
        unset($productData['weight']);
        unset($productData['grand_parent']);
        unset($productData['parent_name']);
        unset($productData['!IMAGE']);
        unset($productData['!META DATA FOR IMAGE']);
        unset($productData['!PRICE CHF']);

        if ($this->languageKeys) {
            foreach ($this->languageKeys as $lang_key) {

                foreach ($productData as $k => $v) {
                    if (!empty($v)) {
                        $optionsData = array();
                        $optionsData['key'] = $lang_key;
                        $optionsData['contentid'] = $product_id;
                        $optionsData['value'] = $v;
                        switch ($k) {
                            case 'SUB COLLECTION RANK':
                                $optionsData['tmplvarid'] = 214;
                                break;
                            case 'REFERENCE RANK':
                                $optionsData['tmplvarid'] = 215;
                                break;
                            case 'EAN':
                                $optionsData['tmplvarid'] = 194;
                                break;
                            case 'MOVEMENT TYPE':
                                $optionsData['tmplvarid'] = 193;
                                break;
                            case 'MOVEMENT CALIBER':
                                $optionsData['tmplvarid'] = 195;
                                break;
                            case 'MOVEMENT ADDITIONAL INFORMATION':
                                $optionsData['tmplvarid'] = 196;
                                break;
                            case 'MOVEMENT ADDITIONAL INFORMATION 2':
                                $optionsData['tmplvarid'] = 197;
                                break;
                            case 'FUNCTION WATCH FINDER':
                                $optionsData['tmplvarid'] = 198;
                                break;
                            case 'FUNCTION WATCH FINDER ADDITIONAL':
                                $optionsData['tmplvarid'] = 199;
                                break;
                            case 'FUNCTIONS LINE 1':
                                $optionsData['tmplvarid'] = 200;
                                break;
                            case 'FUNCTIONS LINE 2':
                                $optionsData['tmplvarid'] = 201;
                                break;
                            case 'FUNCTIONS LINE 3':
                                $optionsData['tmplvarid'] = 202;
                                break;
                            case 'WATER RESISTANCE':
                                $optionsData['tmplvarid'] = 204;
                                break;
                            case 'CASE MATERIAL':
                                $optionsData['tmplvarid'] = 205;
                                break;
                            case 'MATERIAL':
                                $optionsData['tmplvarid'] = 206;
                                break;
                            case 'CASE DIAMETER':
                                $optionsData['tmplvarid'] = 207;
                                break;
                            case 'CASE HEIGHT':
                                $optionsData['tmplvarid'] = 208;
                                break;
                            case 'DIAL COLOUR':
                                $optionsData['tmplvarid'] = 216;
                                break;
                            case 'CRYSTAL':
                                $optionsData['tmplvarid'] = 209;
                                break;
                            case 'CROWN':
                                $optionsData['tmplvarid'] = 210;
                                break;
                            case 'BRACELET COLOUR':
                                $optionsData['tmplvarid'] = 211;
                                break;
                            case 'BRACELET MATERIAL':
                                $optionsData['tmplvarid'] = 212;
                                break;
                            case 'BRACELET DIMENSIONS':
                                $optionsData['tmplvarid'] = 213;
                                break;
                        }

                        if (!empty($optionsData['tmplvarid'])) {

                            $q = $this->modx->newObject('locTemplateVarResource');
                            $q->fromArray($optionsData);
                            $result = $q->save();
                            if (!$result) {
                                $ob = $this->modx->getObject('locTemplateVarResource', $optionsData);

                                if ($ob) {
                                    $ob->fromArray($optionsData);
                                    $ob->save();
                                }
                            }
                        }
                    }
                }
            }

        }

    }

    private function getLanguageKeys()
    {
        $localizatorLanguages = $this->modx->getIterator('localizatorLanguage', array('active' => 1,));
        $lgs = array();
        if ($localizatorLanguages) {
            foreach ($localizatorLanguages as $lg) {
                $lgs[] = $lg->key;
            }
        }

        return $lgs;
    }

    public function createLocalizatorContent($resource_data)
    {
        if ($this->languageKeys) {
            foreach ($this->languageKeys as $lang_key) {
//                $processorProps = array(
//                    'resource_id' => $resource_data['id'],
//                    'key' => $lang_key,
//                    'pagetitle' => $resource_data['pagetitle']
//                );
//
//                $otherProps = array(
//                    'processors_path' => MODX_CORE_PATH . 'components/localizator/processors/'
//                );
//
//                $response = $this->modx->runProcessor('mgr/content/create', $processorProps, $otherProps);

                $q = $this->modx->getObject('localizatorContent', array(
                    'resource_id' => $resource_data['id'],
                    'key' => $lang_key,
                    'pagetitle' => $resource_data['pagetitle']
                ));

                if (!$q) {
                    $q = $this->modx->newObject('localizatorContent');
                    $q->fromArray(array(
                        'resource_id' => $resource_data['id'],
                        'key' => $lang_key,
                        'pagetitle' => $resource_data['pagetitle']
                    ));
                    $q->save();
                }

            }
        }
    }

}