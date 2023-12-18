<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Traits\CommonFunctions;
use Illuminate\Support\Facades\Log;
use App\Interfaces\ProductInterface;
use Carbon\Carbon;

class DownloadProductImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,CommonFunctions;

    /**
     * @var array
     */
    protected $productImageArray = [];

    /**
     * @var ProductInterface
     */
    private ProductInterface $productInterface;

    /**
     * Create a new job instance.
     *
     * @param  mixed $productImageArray
     * @param  ProductInterface $productInterface
     */
    public function __construct($productImageArray,$productInterface)
    {
        $this->productImageArray = $productImageArray;
        $this->productInterface = $productInterface;
    }

    /**
     * Execute the Jobs
     *
     * @return void
     */
    public function handle()
    {
        try{
            if($this->productImageArray) {
                $productImageListWithName = [];
                foreach($this->productImageArray as $productImage)
                {
                    $downloadImageName = $this->downloadProductImage($productImage['image_path'],(string)$productImage['product_id']);
                    $this->copyFile(public_path($this->getImageDownloadDirectoryName().'/'.(string)$productImage['product_id'].'/'.$downloadImageName ),public_path($this->getImageDownloadDirectoryName().'/'.(string)$productImage['product_id'].'_temp/'.'/'.$downloadImageName));
                    if($downloadImageName)
                    {
                        $productImageListWithName[] =
                            [
                                "image_id" =>  $productImage['image_id'],
                                "product_id" => $productImage['product_id'],
                                "image_filename" => $this->getImageDownloadDirectoryName()."/".$productImage['product_id']."/".$downloadImageName,
                                "created_at" => Carbon::now(),
                                "updated_at" => Carbon::now()
                            ];

                    }

                }

                $produtImageArrayGroupByProductId = $this->groupByArray("product_id", $productImageListWithName);

                if(count($produtImageArrayGroupByProductId))
                {
                    $isImageDetailsInsertOrDelete = $this->insertOrDeleteProductImagesData($produtImageArrayGroupByProductId);
                    if($isImageDetailsInsertOrDelete){
                        $this->deleteDuplicateImageDirectory($produtImageArrayGroupByProductId);
                    }
                }
            }

        }catch(\Exception $exception) {
            Log::channel('woocommerce_api')->error("Error Related to When execute the queue ".$exception->getMessage());
        }

    }

    /**
     * Download Product Images to Server
     *
     * @param string $external_link
     * @param string $store_directory_name
     * @return false|string
     */
    public function downloadProductImage($external_link,$store_directory_name)
    {
        $storePath =  public_path($this->getImageDownloadDirectoryName().'/'.$store_directory_name);
        return $this->downloadFileFromExternalSource($external_link,$storePath);
    }

    /**
     * Insert Product Image Data to database
     *
     * @param mixed $produtImageArrayGroupByProductId
     * @return mixed
     */
    public function insertOrDeleteProductImagesData($produtImageArrayGroupByProductId){
        return $this->productInterface->insertOrDeleteMassProductImages($produtImageArrayGroupByProductId);
    }

    /**
     * Delete and rename duplicate image Directory
     *
     * @param mixed $product_image_list
     * @return void
     */
    public function  deleteDuplicateImageDirectory($product_image_list)
    {
        try{
            foreach($product_image_list as $productId => $product_image){
                $this->deleteDirectory($this->getImageDownloadDirectoryName()."/".$productId);
                $this->renameDirectory($this->getImageDownloadDirectoryName()."/".$productId."_temp",$this->getImageDownloadDirectoryName()."/".$productId);
            }

        }catch(\Exception $exception) {
            Log::channel('woocommerce_api')->error("product image rename or delete process failed ".$exception->getMessage());
        }
    }

}
