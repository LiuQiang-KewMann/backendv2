<?php

// Index page
Route::get('/', function () {
    return "KGB Engine App - Please use HTTP to call APIs...";
});


// KGB Game-Engine is the backend of KGB
// Auth
Route::group(['namespace' => 'Auth'], function () {
    Route::controllers([
        'auth' => 'AuthController',
    ]);
});


// APIs should be called by HTTP only
Route::group([
    'prefix' => 'api',
    'middleware' => 'jwt.auth'
], function () {
    // Design APIs: restricted ------------------------------------------------------------------------------------------
    Route::group(['prefix' => 'design', 'namespace' => 'Design'], function () {
        Route::controllers([
            'game' => 'GameController',
            'process' => 'ProcessController',
            'task' => 'TaskController',
            'reward' => 'RewardController',
            'challenge' => 'ChallengeController',
            'component' => 'ComponentController',
            'metric' => 'MetricController',
            'metricItem' => 'MetricItemController',
            'file' => 'FileController',
            'leaderboard' => 'LeaderboardController',
        ]);
    });


    // Admin APIs: restricted ------------------------------------------------------------------------------------------
    Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function () {
        Route::controllers([
            'player' => 'PlayerController',
            'game' => 'GameController',
            'team' => 'TeamController',
            'teamRole' => 'TeamRoleController',
            'leaderboard' => 'LeaderboardController',
        ]);
    });


    // Client APIs: restricted -----------------------------------------------------------------------------------------
    Route::group(['prefix' => 'client', 'namespace' => 'Client'], function () {
        Route::controllers([
            'game' => 'GameController',
            'process' => 'ProcessController',
            'task' => 'TaskController',
            'challenge' => 'ChallengeController',
            'player' => 'PlayerController',
            'comment' => 'CommentController',
        ]);
    });
});