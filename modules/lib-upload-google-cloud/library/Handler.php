<?php
/**
 * Handler
 * @package lib-upload-google-cloud
 * @version 0.0.1
 */

namespace LibUploadGoogleCloud\Library;

use \claviska\SimpleImage;
use LibUpload\Model\Media as _Media;

class Handler implements \LibMedia\Iface\Handler
{
    private static $host  = 'https://storage.googleapis.com/';

    private static function compress(object $result): object{
        return $result;
    }

    private static function imageCompress(object $result, bool $force=false): object{
        if( $webp = self::makeWebP($result, $force) )
            $result->webp = $webp;

        return self::compress($result);
    }

    private static function makeWebP(object $result, bool $force=false): object{
        return self::compress($result);
    }

    static function get(object $opt): ?object{
        $config   = \Mim::$app->config->libUploadGoogleCloud;
        $host     = self::$host . $config->bucket . '/';
        $base     = (object)['host'=>$host];
        $host_len = strlen($host);

        $file_host= substr($opt->file, 0, $host_len);
        if($file_host != $host)
            return null;

        $base_file = substr($opt->file, $host_len);
        $file_name = basename($base_file);
        $file_id   = preg_replace('!\..+$!', '', $file_name);

        $media = _Media::getOne(['identity'=>$file_id]);
        if(!$media)
            return null;

        $file_mime = $media->mime;
        $is_image  = fnmatch('image/*', $file_mime);

        $result = (object)[
            'base' => $base_file,
            'none' => $base->host . $base_file
        ];

        if(!$is_image)
            return self::compress($result);

        list($i_width, $i_height) = [$media->width, $media->height];
        $result->size = (object)[
            'width'  => $media->width,
            'height' => $media->height
        ];

        if(!isset($opt->size))
            return self::makeWebP($result);

        $t_width  = $opt->size->width ?? null;
        $t_height = $opt->size->height ?? null;

        if(!$t_width)
            $t_width = ceil($i_width * $t_height / $i_height);
        if(!$t_height)
            $t_height = ceil($i_height * $t_width / $i_width);

        if($t_width == $i_width && $t_height == $i_height)
            return self::makeWebP($result);

        $suffix    = '_' . $t_width . 'x' . $t_height;
        $base_file = preg_replace('!\.[a-zA-Z]+$!', $suffix . '$0', $base_file);

        $result->none = $base->host . $base_file;
        $file_abs     = $base_file;
        $file_ori_abs = $result->base;

        $result->base = $file_abs;

        $c_width  = $media->width;
        $c_height = $media->height;

        if($c_width == $t_width && $c_height == $t_height)
            return self::makeWebP($result);

        // $exists = MASize::get([
        //     'media' => $media->id,
        //     'size'  => $t_width . 'x' . $t_height
        // ]);
        // if($exists)
        //     return self::makeWebP($result);

        // self::resizeImage((object)[
        //     'path'   => $result->base,
        //     'mime'   => $file_mime,
        //     'source' => $opt->file,
        //     'width'  => $t_width,
        //     'height' => $t_height
        // ]);

        // MASize::create([
        //     'user'      => (\Mim::$app->user->id ?? 0),
        //     'media'     => $media->id,
        //     'size'      => $t_width . 'x' . $t_height,
        //     'compress'  => 'none'
        // ]);

        return self::makeWebP($result);
    }
}