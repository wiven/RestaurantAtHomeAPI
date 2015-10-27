<?php
/**
 * Created by PhpStorm.
 * User: TDP-DEV
 * Date: 21-Sep-15
 * Time: 07:52 PM
 */

namespace Rath\Helpers;


use Rath\Libraries\UploadHandler;

class PhotoManagement
{
    const PhotoDir = "files";
    const ThumbnailDir = "thumbnail";

    public static function deletePhoto($url)
    {
        $result = false;
        $file = APP_PATH."\\".self::PhotoDir."\\".$url;
        if(file_exists($file))
            $result = unlink($file);

        $thumb = APP_PATH."\\".self::PhotoDir."\\".self::ThumbnailDir."\\".$url;
        if(file_exists($thumb))
            $result = unlink($thumb) & $result;

        return $result;
    }

    /**
     * @param $path
     * @return array
     */
    public static function getPhotoUrls($path)
    {
        if(isset($path))
        {
            $uploader = new UploadHandler();
            return [
                "url" => $uploader->get_download_url($path),
                "thumbnailUrl" => $uploader->get_download_url($path,"thumbnail") //thumbnail is a "version" of the image.
            ];
        }
    }

    public static function getPhotoUrlsForArray($array, $column)
    {
        for($i = 0; $i < count($array); $i++){
            $photo = $array[$i];
            //var_dump($photo);
            $photo[$column] = PhotoManagement::getPhotoUrls($photo[$column]);
            $array[$i] = $photo;
        }
        return $array;
    }
}