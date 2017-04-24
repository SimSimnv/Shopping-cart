<?php


namespace AppBundle\Service;

use AppBundle\Entity\Product;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $targetDir;

    public function __construct($targetDir)
    {
        $this->targetDir = $targetDir;
    }
    public function upload(Product $product)
    {
        $imgName='default.jpg';
        if ($product->getImage() instanceof UploadedFile){
            /**@var UploadedFile $img**/
            $img=$product->getImage();
            $imgName=md5($product->getName().uniqid()).'.'. $img->guessExtension();
            $img->move($this->targetDir, $imgName);
            $product->setImage($imgName);
        }
        return $imgName;
    }

    public function getTargetDir()
    {
        return $this->targetDir;
    }
}