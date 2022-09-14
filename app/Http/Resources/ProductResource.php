<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 9/14/2022
 * Time: 9:41 PM
 */

namespace App\Http\Resources;

use App\Service\ProductService;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'sku'      => $this['sku'],
            'name'     => $this['name'],
            'category' => $this['category'],
            'price'    => (new ProductService())->getProductFinalPrice($this)
        ];
    }
}