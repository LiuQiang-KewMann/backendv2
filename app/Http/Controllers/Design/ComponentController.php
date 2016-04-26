<?php namespace App\Http\Controllers\Design;

use Request;
use Response;
use App\Models\Challenge;
use App\Models\Component;
use App\Models\JsonSchema;
use App\Http\Controllers\Controller;

class ComponentController extends Controller
{
    public function getList($challengeId)
    {
        $challenge = Challenge::find($challengeId);

        return [
            'items' => $challenge->components,
            'parent' => $challenge,
            'schema_create' => JsonSchema::items('component', 'create'),
            'instance_create' => JsonSchema::defaultValues('component', 'create'),
        ];
    }


    public function getDetail($id)
    {
        $item = Component::find($id);

        return [
            'item' => $item->detail(),
            'schema_edit' => JsonSchema::items('component', 'edit_' . $item->jsonGet('type')),
            'schema_config' => JsonSchema::items('component', 'config')
        ];
    }


    public function postCreate($challengeId)
    {
        $sequence = (Component::where('challenge_id', $challengeId)->max('sequence') ?: 0) + 1;
        $updateArray = Request::only(JsonSchema::names('component', 'create'));

        $item = Component::create([
            'challenge_id' => $challengeId,
            'sequence' => $sequence
        ])->jsonUpdate($updateArray);

        $type = Request::get('type');

        return [
            'item' => $item->detail(),
            'schema_edit' => JsonSchema::items('challenge', "edit_$type"),
            'msg' => 'component_created'
        ];
    }


    public function postUpdate($id)
    {
        $item = Component::find($id);
        $type = $item->jsonGet('type');
        $updateArray = Request::only(JsonSchema::names('component', "edit_$type"));

        $item->jsonUpdate($updateArray);

        return [
            'item' => $item->detail(),
            'msg' => 'component_updated'
        ];
    }


    public function postConfig($id)
    {
        $item = Component::find($id);
        $type = Request::get('type');
        $updateArray = Request::only(JsonSchema::names('component', 'config'));

        // reset operator to be free
        array_set($updateArray, 'operator', Component::OPERATOR_FREE);
        // reset solution
        array_set($updateArray, 'solution', null);

        $item->jsonUpdate($updateArray);

        return [
            'item' => $item->detail(),
            'schema_edit' => JsonSchema::items('component', "edit_$type"),
            'msg' => 'component_configured'
        ];
    }


    public function postDelete($id)
    {
        Component::find($id)->delete();

        return ['msg' => 'deleted'];
    }


    public function postReorder()
    {
        $items = Request::get('items');
        foreach ($items as $item) {
            $id = $item['id'];
            $sequence = $item['sequence'];

            Component::where('id', $id)->update(['sequence' => $sequence]);
        }

        return ['msg' => 'reordered'];
    }
}