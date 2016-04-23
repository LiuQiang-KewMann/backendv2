<?php namespace App\Traits;

use App\Models\Action;

trait DesignActionTrait
{
    static public function syncList($gameCode)
    {
        // get action headers from engine
        $actionsHeaders = self::connByGameCodeAndEnv($gameCode)
            ->get('/design/versions/latest/actions');

        // delete those not returned by engine but exist in local db
        Action::where('game_code', $gameCode)->whereNotIn('code', array_column($actionsHeaders, 'id'))->delete();

        // create or update actions
        foreach ($actionsHeaders as $actionsHeader) {
            // code
            $code = strtoupper($actionsHeader['id']);
            // fullCode: e.g. G001.P01
            $fullCode = strtoupper($gameCode . '.' . $code);

            Action::updateOrCreate([
                'game_code' => $gameCode,
                'code' => $code
            ], [
                'fullcode' => $fullCode,
                'json_default' => json_encode([
                    'code' => $code,
                    'fullcode' => $fullCode,
                ])
            ]);
        }
    }


    public function syncDesign()
    {
        // get design from engine
        $design = self::connByGameCodeAndEnv($this->game_code)->get('/design/versions/latest/actions/' . $this->code);

        // update remote json
        $this->update([
            'json_remote' => json_encode($design)
        ]);
    }
}