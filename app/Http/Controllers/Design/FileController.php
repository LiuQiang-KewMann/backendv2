<?php namespace app\Http\Controllers\Design;

use App\Http\Controllers\Controller;
use App\Models\FileManager;
use App\Models\Game;
use Request;
use Storage;
use Response;

class FileController extends Controller
{
    protected $folderTypeArray = [
        FileManager::FOLDER_LIB,
        FileManager::FOLDER_APP,
        FileManager::FOLDER_USER,
    ];

    protected $fileTypeExtensions = [
        'image' => ['png', 'jpg'],
        'audio' => ['mp3']
    ];

    public function getList($gameId, $folderType, $fileType)
    {
        // folderType passed in must be one of the predefined folder types
        if (!in_array($folderType, $this->folderTypeArray)) {
            return Response::json(['msg' => 'invalid_folder_type'], 406);
        }

        $game = Game::find($gameId);
        $files = Storage::files($game->jsonGet('code') . '/' . $folderType);


        $allowedFileTypes = array_keys($this->fileTypeExtensions);
        if (!in_array($fileType, $allowedFileTypes)) {
            return ['items' => []];

        }

        $fileExtensions = array_get($this->fileTypeExtensions, $fileType, []);
        foreach ($files as $key => $file) {
            $pathInfo = pathInfo($file);
            // filter out those not in the extension list
            if (!in_array($pathInfo['extension'], $fileExtensions)) {
                array_forget($files, $key);
            }
        }

        // differentiate normal file and thumb
        $filesNotThumb = [];
        $filesThumb = [];
        foreach ($files as $file) {
            if (str_contains($file, '_thumb')) {
                // thumb file
                $pathInfo = pathInfo($file);
                array_set($filesThumb, str_replace('_thumb', '', $pathInfo['filename']), $file);

            } else {
                // not thumb file
                array_push($filesNotThumb, $file);
            }
        }

        $items = [];
        foreach ($filesNotThumb as $fileNotThumb) {
            $pathInfo = pathInfo($fileNotThumb);
            $filename = $pathInfo['filename'];

            array_push($items, [
                'path' => $fileNotThumb,
                'thumb' => in_array($filename, array_keys($filesThumb)) ? array_get($filesThumb, $filename) : $fileNotThumb
            ]);
        }

        return ['items' => $items];
    }


    public function postUpload($gameId, $folderType)
    {
        // folderType passed in must be one of the predefined folder types
        if (!in_array($folderType, $this->folderTypeArray)) {
            return Response::json(['msg' => 'invalid_folder_type'], 406);
        }

        // if no file attached
        if (!Request::hasFile('file')) {
            return Response::json(['msg' => 'file_not_attached'], 406);
        }

        $game = Game::find($gameId);
        $file = Request::file('file');
        $path = FileManager::put($game->jsonGet('code'), $folderType, $file);
        $item = ['path' => $path];

        // make thumb if file uploaded is image
        $extension = $file->getClientOriginalExtension();
        $imageExtensions = array_get($this->fileTypeExtensions, 'image', []);
        if (in_array(strtolower($extension), $imageExtensions)) {
            $thumb = FileManager::putThumb($file, $path);
            array_set($item, 'thumb', $thumb);
        }

        return Response::json([
            'msg' => 'done',
            'item' => $item
        ]);
    }


    public function postDelete()
    {
        $path = Request::get('path');
        $pathInfo = pathInfo($path);
        $thumb = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.' . $pathInfo['extension'];

        // delete
        FileManager::delete([$path, $thumb]);
    }
}
