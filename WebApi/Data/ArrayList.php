<?php

namespace Data;

/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 5-9-2017
 * Time: 20:40
 */
class ArrayList
{
    private $currentPrediction;
    private $internalArray = [];

    function __construct($array)
    {
        $this->internalArray = $array;
    }

    public function select($prediction)
    {
        $this->currentPrediction = $prediction;
        $newArray = [];
        foreach ($this->internalArray as $item) {
            $newArray[] = call_user_func($this->currentPrediction, $item);
        }
        return new ArrayList($newArray);
    }

    public function skip($skip)
    {
        return new ArrayList(array_slice($this->internalArray, $skip));
    }

    public function take($take)
    {
        return new ArrayList(array_slice($this->internalArray, 0, $take));
    }

    public function orderBy($prediction)
    {
        $this->currentPrediction = $prediction;
        usort($this->internalArray, function ($a, $b) {
            $a = call_user_func($this->currentPrediction, $a);
            $b = call_user_func($this->currentPrediction, $b);
            return $a == $b ? 0 : ($a > $b ? 1 : -1);
        });
        return $this;
    }

    public function orderByDescending($prediction)
    {
        return $this->orderBy($prediction)->reverse();
    }

    public function reverse()
    {
        return new ArrayList(array_reverse($this->internalArray));
    }

    public function toArray()
    {
        return $this->internalArray;
    }
}
