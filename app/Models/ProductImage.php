<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
class ProductImage extends Model
{
    use HasFactory;

    /**
     * Table Name Associated with  Model.
     *
     * @var string
     */
    protected $table='product_images';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'image_id',
        'product_id'
    ];

    /**
     * Get Product relevant to product Image
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function getProduct()
    {
        return $this->belongsTo(Product::class,'product_id');
    }
}
