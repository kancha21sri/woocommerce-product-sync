<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use  App\Models\Product;
use  App\Models\ProductImage;
use App\Interfaces\ProductInterface;

class ProductController extends Controller
{
    /**
     * @var ProductInterface
     */
    private ProductInterface $productInterface;

    /**
     * @param ProductInterface $productInterface
     */
    public function __construct(ProductInterface $productInterface)
    {
        $this->productInterface = $productInterface;
    }

    /**
     * Get Product List
     *
     * @return mixed
     */
    public function list()
    {
        return $this->productInterface->getProductList();
    }
}
