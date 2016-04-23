<?php namespace App\Traits;

use App\Models\JsonSchema;

trait DesignGameTrait
{
    public function syncDesign()
    {
        $res = $this->connByGame($this)->get('/admin/', [])['game'];

        // filter out those editable
        $updateJson = array_except($res, JsonSchema::names('game', 'edit'));

        $this->jsonUpdate($updateJson);
    }
}