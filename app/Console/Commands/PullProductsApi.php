<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Traits\APIConnection;
use App\Interfaces\ProductInterface;
use App\Traits\CommonFunctions;
use App\Jobs\DownloadProductImages;

class PullProductsApi extends Command
{

    use APIConnection ,CommonFunctions;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Products from Woocommrece API';
    private ProductInterface $productInterface;

    /**
     * @param ProductInterface $productInterface
     */
    public function __construct(ProductInterface $productInterface)
    {
        $this->productInterface = $productInterface;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $productList = $this->connectProductsEndPoint();
            if ($productList) {
                $this->info('Total Products Count (From Woo-commerce End) is ' . count($this->connectProductsEndPoint()));
                $procceProductsdData = $this->preProcessProductDataBeforeSync($productList);
                $this->info('Total Processed Products Count is ' . count($procceProductsdData));
                if ($procceProductsdData) {
                    $prodcutInsertUpdateResponse = $this->productInterface->insertOrUpdateMassProduct($procceProductsdData);
                    if($prodcutInsertUpdateResponse) {
                        $this->info(count($procceProductsdData). " Products Insert/Update with Database");
                    } else {
                        $this->info("Product Insert/Update Delete Process Failed. please check the woocommerce-api.log file");
                    }
                }

                $processedProductImageData = $this->preProcessProductImageDataBeforeSync($productList);
                if(count($processedProductImageData) > 0){
                    $processStatus = true;
                    foreach($processedProductImageData as $product){
                        $folderCreateStatus = $this->createDirectory(config('woocommerce_api_config.image_store_folder_name'),(string)$product['product_id']);
                         if($folderCreateStatus){
                            $this->createDirectory(config('woocommerce_api_config.image_store_folder_name'),$product['product_id']."_temp");
                       }
                        if(!$folderCreateStatus){
                            $this->info('Product Image Store Folders Creation Process Failed. Please check please check the woocommerce-api.log file');
                            $processStatus = false;
                            break;
                        }
                    }

                    if($processStatus){
                        $this->info('Latest Product Images Added Queue Job.Please Execute the Queue (If not run)');
                        dispatch(new DownloadProductImages($processedProductImageData,$this->productInterface));
                    } else{
                        return false;
                    }
                }

            } else {
                $this->info('There is a Woocommerce API Connectivity Problem. please check the woocommerce-api.log file');
            }
        }catch (\Exception $exception){
            Log::channel('woocommerce_api')->error("Woocommerce API Product Sync Related Issue ".$exception->getMessage());
        }

        return Command::SUCCESS;
    }

    Public function preProcessProductDataBeforeSync($products_array)
    {
        $processProductArray = [];
        foreach ($products_array as $product)
        {
            $processProductArray[] = [
                'name' =>  $product['name'],
                'product_id' => $product['id'],
                'sku' => $product['sku'],
                'price' => $product['price'],
                'description' => $this->stripTagsContent($product['description'])
            ];
        }
        return $processProductArray;
    }

    public function preProcessProductImageDataBeforeSync($products_array)
    {
        $processProductImageArray = [];
        foreach ($products_array as $product)
        {
            $productId = $product['id'];
            if (isset($product['images']) && count($product['images']) > 0) {
                foreach($product['images'] as  $productImage){
                    $processProductImageArray[]= [
                        "image_id" =>  $productImage['id'],
                        "product_id" => $productId ,
                        "image_path" => $productImage['src']
                    ];
                }
            }
        }

        return $processProductImageArray;
    }

}
