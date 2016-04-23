<?php namespace App\Listeners;

use App\Events\ImageUploaded;
use App\Models\ChallengeSet;
use App\Models\Game;
use App\Models\Metric;
use App\Models\Process;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateImageRelPathAttribute
{
    public function __construct()
    {
        //
    }

    public function handle(ImageUploaded $event)
    {
        $fullcode = $event->fullcode;
        $relPath = $event->relPath;

        $codes = explode('.', $fullcode);

        if (sizeof($codes) == 1) {
            // game
            foreach (Game::where('code', $fullcode)->get() as $game) {
                $game->jsonUpdate([
                    'image_rel_path' => $relPath
                ]);
            }

        } else {
            $modelCode = $codes[1];

            // sub model: process, challengeSet and etc
            switch (substr($modelCode, 0, 1)) {
                case 'P':
                    // process
                    Process::firstOrNew(['fullcode'=>$fullcode])->jsonUpdate([
                        'image_rel_path' => $relPath
                    ]);
                    break;

                case 'C':
                    // challengeSet
                    ChallengeSet::firstOrNew(['fullcode'=>$fullcode])->jsonUpdate([
                        'image_rel_path' => $relPath
                    ]);
                    break;

                case 'M':
                    // metric
                    Metric::firstOrNew(['fullcode'=>$fullcode])->jsonUpdate([
                        'image_rel_path' => $relPath
                    ]);
                    break;

                default:
                    break;
            }
        }
    }
}