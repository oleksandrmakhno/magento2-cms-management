<?php

namespace Overdose\CMSContent\Observer;

use Overdose\CMSContent\Model\BackupManager;

class CmsSaveBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Events map to process
     *
     * @var array
     */
    private $eventsTypeMap = [
        'cms_block_save_before' => BackupManager::TYPE_CMS_BLOCK,
        'cms_page_save_before' => BackupManager::TYPE_CMS_PAGE,
    ];

    /**
     * Add keys to check if data was changed
     *
     * @var array
     */
    private $keysToCheck = [
        'identifier',
        'title',
        'content',
        'content_heading',
    ];

    /**
     * @var BackupManager
     */
    private $backupManager;

    /**
     * CmsSaveBefore constructor.
     *
     * @param BackupManager $backupManager
     */
    public function __construct(
      BackupManager $backupManager
    ) {
        $this->backupManager = $backupManager;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $eventName = $observer->getEvent()->getName();

        if (empty($this->eventsTypeMap[$eventName])) {
            return;
        }

        $cmsObject = $observer->getEvent()->getData('data_object');
        /* Original Model::hasDataChanges() not works here, so we added our custom check */
        if ($this->hasImportantDataChanges($cmsObject)){
            $this->backupManager->createBackup($this->eventsTypeMap[$eventName], $cmsObject);
        }
    }

    /**
     * Check if cms object was changed
     *
     * @param $cmsObject
     * @return bool
     */
    private function hasImportantDataChanges($cmsObject)
    {
        foreach ($this->keysToCheck as $key) {
            if ($cmsObject->getData($key) !== $cmsObject->getOrigData($key)) {
                return true;
            }
        }

        return false;
    }
}
