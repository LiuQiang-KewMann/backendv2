<?php namespace App\Jobs;

use App\Contracts\GameEngine;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;

class GameEngineJob extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $gameId;
    protected $engineFunction;
    protected $params;

    /**
     * Create a new job instance.
     *
     * @param string $gameId
     * @param string $engineFunction
     * @param array $params
     *
     * @return void
     */
    public function __construct($gameId, $engineFunction, $params=[])
    {
        $this->gameId = $gameId;
        $this->engineFunction = $engineFunction;
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @param GameEngine $engine
     *
     * @return void
     */
    public function handle(GameEngine $engine)
    {
        $engineFunction = $this->engineFunction;
        $engine->conn($this->gameId)->$engineFunction($this->params);
    }
}
