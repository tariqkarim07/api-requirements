<?php
/**
 * Created by PhpStorm.
 * User: tariq
 * Date: 9/14/2022
 * Time: 8:29 PM
 */

namespace App\Service;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class ProductService
{
    /**
     * @return array
     */
    public function getProducts(): array
    {

        try {
            $min_price = request()->input('min_price');
            $max_price = request()->input('max_price');
            $min_discount = request()->input('min_discount');
            $max_discount = request()->input('max_discount');
            $category = request()->input('category');

            //read data from json file
            $product_set = File::get(storage_path() . '/product_list.json');
            $product_set = json_decode($product_set, true);

            $products = collect($product_set['products']); //make collection to apply eloquent query


            $category_discount = $product_set['discounts']['category']; //fetch category based discounts
            $sku_discount = $product_set['discounts']['sku_discount']; //get sku based discounts

            //Apply filters based on query paravmentes
            $products = $products
                 ->when($min_price>0, function ($query) use ($min_price) {
                return $query->where('price', '>=', $min_price);
                })

                ->when(isset($max_price) && $max_price>0, function ($query) use ($max_price) {
                    return $query->where('price', '<=', $max_price);
                })

                ->when(isset($min_discount) && $min_discount>0, function ($query) use ($min_discount) {
                    return $query->where('price', '>=', $min_discount);
                })

                ->when(isset($max_discount) && $max_discount>0, function ($query) use ($max_discount) {
                    return $query->where('price', '<=', $max_discount);
                })

                ->when(!empty($category), function ($query) use ($category) {
                    return $query->where('category', $category);
                });

             //Attach the discount percentages with each products
            $products = $products->map(function ($item, $key) use ($category_discount, $sku_discount){

                $item['discount'] = null;
                if(isset($category_discount[$item['category']])
                    && is_numeric($category_discount[$item['category']])
                    && $category_discount[$item['category']] >0 )
                     $item['discount'] = $category_discount[$item['category']];

                else if(isset($sku_discount[$item['sku']])
                    && is_numeric($sku_discount[$item['sku']])
                    && $sku_discount[$item['sku']] >0 )

                     $item['discount'] = $sku_discount[$item['sku']];

                return $item;
            });

            return $products->toArray();
        }catch (\Exception $e){
            Log::error($e);
            return array();
        }
    }

    /**
     * @param $product
     * @return array
     */
    public function getProductFinalPrice($product): array
    {
        try{
            $product_price =  (int)$product['price']; // product price will always be interger : requriement
            $price = array(
                "original" => $product_price,
                "final" => $product_price,
                "discount_percentage" => ($product['discount']!=null)?$product['discount']."%":null,
                "currency" => "EUR",
            );

            if(!isset($product['discount']))
                return  $price;

            if($product['discount']<=0)
                return  $price;

            $price['final'] = $product_price - ($product_price * ($product['discount']/100));

            return $price;

        }catch (\Exception $e){
            Log::error($e);
            return array();
        }
    }
}