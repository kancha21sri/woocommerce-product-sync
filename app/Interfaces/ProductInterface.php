<?php

namespace App\Interfaces;

interface ProductInterface
{
    /**
     * Get Product List
     *
     * @return mixed
     */
    public function getProductList();

    /**
     * insert or update mass products
     *
     * @param mixed $product_list
     * @return mixed
     */
    public function insertOrUpdateMassProduct($product_list);

    /**
     * insert or delete mass product image data
     *
     * @param mixed $product_image_list
     * @return mixed
     */
    public function insertOrDeleteMassProductImages($product_image_list);


}
