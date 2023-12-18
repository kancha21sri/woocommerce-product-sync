<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ProductImage;

class Product extends Model
{
    use HasFactory;

    /**
     * Table Name Associated with  Model.
     *
     * @var string
     */
    protected $table='products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'product_id',
        'sku',
        'price',
        'description',
    ];

    /**
     * Get the Product Images from Product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getProductImages()
    {
        return $this->hasMany(ProductImage::class,'product_id','product_id');
    }
}
