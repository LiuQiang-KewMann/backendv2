<?php namespace app\Models;

use App\Traits\JsonTrait;

class JsonSchema extends BaseModel
{
    use JsonTrait;

    /*
     * return object
     */
    static public function byModelAndScenario($model, $scenario)
    {
        return self::firstOrNew([
            'model' => $model,
            'scenario' => $scenario
        ]);
    }


    /*
     * get all items, if parent exists, then include parent items as well
     * parent is the same model while scenario is with no underscore
     * e.g. 'edit' is parent of 'edit_radio'
     */
    static public function items($model, $scenario)
    {
        $schema = self::byModelAndScenario($model, $scenario);
        $schemaItems = $schema->jsonGet('items', []);

        // if scenario got underscore _
        if(str_contains($scenario, '_')) {
            // looking for parent
            $parentSchema = self::byModelAndScenario($model, explode('_', $scenario)[0]);
            $parentSchemaItems = $parentSchema->jsonGet('items', []);

            // merge
            $schemaItems = array_merge($schemaItems, $parentSchemaItems);
        }

        return $schemaItems;
    }


    /*
     * get all names
     */
    static public function names($model, $scenario)
    {
        return array_column(self::items($model, $scenario), 'name');
    }


    /*
     * get default values (if defined)
     */
    static public function defaultValues($model, $scenario = 'edit')
    {
        $items = self::items($model, $scenario);
        $itemWithDefaultValues = array_where($items, function ($key, $value) {
            return array_key_exists('default', $value);
        });

        $defaultValues = [];
        foreach ($itemWithDefaultValues as $item) {
            array_set($defaultValues, $item['name'], $item['default']);
        }

        return $defaultValues;
    }
}