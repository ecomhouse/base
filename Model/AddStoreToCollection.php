<?php
declare(strict_types=1);

namespace EcomHouse\Base\Model;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class AddStoreToCollection
{
    private StoreManagerInterface $storeManager;

    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    public function execute(AbstractCollection $collection, array $result, string $columnName): void
    {
        $storesData = [];
        foreach ($result as $storeData) {
            $storesData[$storeData[$columnName]][] = $storeData['store_id'];
        }

        foreach ($collection as $item) {
            $linkedId = $item->getData($columnName);
            if (!isset($storesData[$linkedId])) {
                continue;
            }
            $storeIdKey = array_search(Store::DEFAULT_STORE_ID, $storesData[$linkedId], true);
            if ($storeIdKey !== false) {
                /** @var Store[] $stores */
                $stores = $this->storeManager->getStores(false, true);
                /** @var Store $store */
                $store = current($stores);
                $storeId = $store->getId();
                $storeCode = key($stores);
            } else {
                $storeId = current($storesData[$linkedId]);
                $storeCode = $this->storeManager->getStore($storeId)->getCode();
            }
            $item->setData('_first_store_id', $storeId);
            $item->setData('store_code', $storeCode);
            $item->setData('store_id', $storesData[$linkedId]);
        }
    }
}
