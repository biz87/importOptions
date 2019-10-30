<?php

class importOptionsUploadProcessor extends modObjectProcessor
{
//    public $objectType = 'modResource';
//    public $classKey = 'modResource';
    public $languageTopics = ['importoptions'];
    //public $permission = 'save';

    /**
     * {@inheritDoc}
     * @return boolean
     */
    public function initialize()
    {
        return true;
    }

    public function process()
    {
        $filename = $this->getProperty('file');

        if (!empty($filename)) {
            $importOptions = $this->modx->getService('importOptions', 'importOptions', MODX_CORE_PATH . 'components/importoptions/', array());

            if (!$importOptions) {
                return $this->failure('Could not load importOptions class!');
            }



            $data = $importOptions->getSheet(MODX_BASE_PATH . $filename);
            foreach($data as $row){
                $isExistsProduct = $importOptions->isExistsProduct($row['pagetitle']);
                if($isExistsProduct){
                    $row = $importOptions->getParent($row);
                    $row['id'] = $isExistsProduct->id;
                    $importOptions->updateProduct($row, $isExistsProduct);
                    $importOptions->updateOptions($row);

                    sleep(0.5);
                }
            }

            $msg = $this->modx->lexicon('importoptions_file_uploaded');
            return $this->modx->error->success($msg);
        } else {
            $error = $this->modx->lexicon('importoptions_file_nf');
            return $this->failure($error);
        }
    }


}

return 'importOptionsUploadProcessor';
