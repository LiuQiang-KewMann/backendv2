<?php namespace App\Models;

use Carbon\Carbon;
use League\Flysystem\FileNotFoundException;
use Storage;
use File;
use Image;

/*
 * this is just a helper class, not a eloquent model
 * all functions are static
 * 
 * Todo: convert to Facade
 */
class FileManager
{
    const FOLDER_APP = 'app';
    const FOLDER_USER = 'user';
    const FOLDER_LIB = 'lib';


    /*
     * generate a random sortable string path
     */
    public static function generateRelPath($gameCode, $folder, $extension)
    {
        $fileName = Carbon::now()->timestamp . str_random(5);
        $extension = strtolower($extension);

        return "$gameCode/$folder/$fileName.$extension";
    }


    /*
     * try to delete file by path or path array
     */
    public static function delete($paths)
    {
        if (is_array($paths)) {
            foreach($paths as $path) {
                self::deleteOne($path);
            }

        } else if (is_string($paths)) {
            self::deleteOne($paths);
        }
    }

    
    /*
     * delete one file by path
     */
    protected static function deleteOne($path)
    {
        if (!$path) return;

        try {
            Storage::delete($path);

        } catch (FileNotFoundException $e) {
            return;
        }
    }


    /*
     * put one file to storage, return path
     */
    public static function put($gameCode, $folder, $file)
    {
        $extension = self::getExtension($file);

        $path = self::generateRelPath($gameCode, $folder, $extension);
        Storage::put($path, file_get_contents($file));

        return $path;
    }


    // thumb file name is abc_thumb.png
    public static function putThumb($file, $path)
    {
        $width = env('KGB_IMAGE_THUMB_WIDTH', 100);

        // process image
        $image = Image::make($file)
            ->resize($width, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('png');

        // generate path - thumb always be with png extension
        $pathInfo = pathinfo($path);
        $thumbPath = $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '_thumb.png';

        // save
        Storage::put($thumbPath, $image->getEncoded());

        // END
        return $thumbPath;
    }


    /*
     * get extension by file mime type
     */
    public static function getExtension($file)
    {
        $mimeType = File::mimeType($file);

        return array_get([
            'image/png' => 'png',
            'image/jpeg' => 'jpg',
            'audio/mpeg' => 'mp3'
        ], $mimeType);
    }
}