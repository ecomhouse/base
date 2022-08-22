<?php
declare(strict_types=1);

namespace EcomHouse\Base\Model;

use Magento\Catalog\Model\ImageUploader;
use Magento\Framework\App\Filesystem\DirectoryList;

class ImageProcessor
{
    private ImageUploader $imageUploader;
    private array $images;
    private string $imagePath;

    public function __construct(
        ImageUploader $imageUploader,
        string $imagePath = '',
        array $images = []
    ) {
        $this->imageUploader = $imageUploader;
        $this->images = $images;
        $this->imagePath = $imagePath;
    }

    public function processImages(array $data): array
    {
        foreach ($this->images as $image) {
            if (isset($data[$image][0]['name'])) {
                if (isset($data[$image][0]['tmp_name'])) {
                    // handle new image upload
                    $fileNameToMove = $data[$image][0]['name'];
                    $data[$image] = sprintf(
                        '/%s/%s/%s',
                        DirectoryList::MEDIA,
                        $this->imagePath,
                        ltrim($fileNameToMove, '/')
                    );
                    $this->imageUploader->moveFileFromTmp($fileNameToMove);
                } else {
                    // handle select from gallery
                    $data[$image] = ltrim($data[$image][0]['url'], '/');
                }
            } elseif (isset($data[$image][0]['image']) && !isset($data[$image][0]['tmp_name'])) {
                // handle not changed image
                $data[$image] = $data[$image][0]['image'];
            } else {
                $data[$image] = null;
            }
        }

        return $data;
    }
}
