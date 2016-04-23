<?php namespace App\Traits;

trait JsonTrait
{
    public function jsonArray()
    {
        $jsonArray = json_decode($this->json, true);

        return $jsonArray ?: [];
    }


    public function jsonString()
    {
        return json_encode($this->jsonArray(), JSON_PRETTY_PRINT);
    }


    public function jsonGet($key, $default = null)
    {
        return array_get($this->jsonArray(), $key, $default);
    }


    public function jsonUpdate($array)
    {
        $jsonArray = $this->jsonArray();
        $newJsonArray = array_merge($jsonArray, $array);

        foreach ($array as $key => $value) {
            if (!$value) {
                // remove those with null value
                array_forget($newJsonArray, $key);

            } else if (is_string($value) && strlen($value) == 0) {
                // remove those with empty string value
                array_forget($newJsonArray, $key);

            } else if ($value === 'true') {
                // convert bool in string to PHP bool
                array_set($newJsonArray, $key, true);

            } else if ($value === 'false') {
                // convert bool in string to PHP bool
                array_set($newJsonArray, $key, false);
            }
        }

        $this->update([
            'json' => json_encode(array_sort_recursive($newJsonArray))
        ]);

        return $this;
    }
}