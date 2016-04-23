<?php namespace App\Traits;

trait RuntimeChallengeTrait
{
    /*
     * instance will return an array of default component values
     */
    public function instance()
    {
        $instance = [];
        $components = $this->components;

        foreach ($components as $component) {
            $default = $component->jsonGet('default');

            if ($default != null) {
                array_set($instance, $component->id, $default);
            }
        }

        return $instance;
    }
}