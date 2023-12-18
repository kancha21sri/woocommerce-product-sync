<?php

namespace App\Repositories;

use App\Interfaces\ProductInterface;
use App\Traits\APIResponse;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use DB;
use Illuminate\Support\Facades\Log;
class ProductRepository implements ProductInterface
{
    use APIResponse;

    /**
     * Get Product List From Database
     *
     * @return JsonResponse
     */
    public function getProductList()
    {
        try {
            $productsWithImage = Product::with('getProductImages')->get();
            $productList = $productsWithImage->map(function($product){
                $imagePathList = [];
                foreach ($product->getProductImages as $productImage){
                    $imagePathList[] = url('/'.$productImage->image_filename);
                }
                return [
                    "id" =>   $product->product_id,
                    "name" => $product->name,
                    "sku" => $product->sku,
                    "description" => $product->description,
                    "price" => $product->price,
                    "images" => $imagePathList,
                ];
            });
            return response()->json(['message' => 'Products', 'code' => 200, 'error' => false, 'results' => $productList], 200);

        } catch(\Exception $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }

    }

    /**
     * Insert/Update Mass Records in Product Table
     *
     * @param mixed $product_list
     * @return bool
     */
    public function insertOrUpdateMassProduct($product_list)
    {
        $status = false;
        try {
            $insertUpdatedProductList =collect($product_list)->each(function (array $row) {
                $product = Product::updateOrCreate(
                    ['product_id' => $row['product_id'],],
                    ['name' => $row['name'], 'product_id' => $row['product_id'],'sku' => $row['sku'],'price' => $row['price'],'description' => $row['description']]
                );
            });

            if(count($insertUpdatedProductList) > 0 ){
                $status = true;
            }

        }catch(\Exception $exception) {
            Log::channel('woocommerce_api')->error("product insert or update failed ".$exception->getMessage());
        }
        return  $status;
    }

    /**
     * Insert or Delete Set of Product Images
     *
     * @param mixed $product_images_list
     * @return bool
     */
    public function insertOrDeleteMassProductImages($product_images_list)
    {
        $status = false;
        try {
            DB::beginTransaction();
            foreach($product_images_list as $productId => $productImages) {
                ProductImage::where('product_id', $productId)->delete();
                ProductImage::insert($productImages);
            }

            $status = true;
            DB::commit();

        }catch(\Exception $exception) {
            Log::channel('woocommerce_api')->error("product image information delete or insert failed".$exception->getMessage());
            DB::rollBack();
            $status = false;

        }
        return $status;
    }

}
