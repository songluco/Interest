<?php
namespace App\TransFormer;
/**
 * Created by PhpStorm.
 * User: songlu
 * Date: 2017/8/9
 * Time: 下午7:37
 */
abstract class BaseTransformer
{
    public function transformerCollection($item)
    {
        return array_map([$this, 'transformer'], $item);
    }

    abstract function transformer($item);
}