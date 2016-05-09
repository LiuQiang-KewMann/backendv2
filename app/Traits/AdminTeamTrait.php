<?php namespace App\Traits;

use App\Models\GameUserTeamRole;
use App\Models\JsonSchema;
use App\Models\Team;

/*
 * Please be noted that team in KGB is team instance in Playlyfe
 */
trait AdminTeamTrait
{
    public function createRemoteInstance()
    {
        $res = $this->conn()->post('/admin/teams', [], [
            'name' => $this->jsonGet('name'),
            'definition' => $this->jsonGet('definition')
        ]);

        $this->remote_id = $res['id'];
        $this->json = json_encode($res);
    }


    public function deleteRemoteInstanceIfThere()
    {
        $this->conn()->delete('/admin/teams/' . $this->remote_id, []);
    }
}