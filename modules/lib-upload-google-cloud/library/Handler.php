<?php
/**
 * Handler
 * @package lib-upload-google-cloud
 * @version 0.0.1
 */

namespace LibUploadGoogleCloud\Library;

use LibGoogleCloud\Library\Auth;
use LibCurl\Library\Curl;

class Handler implements \LibMedia\Iface\Handler
{
    private static $host  = 'https://storage.googleapis.com/';
    private static $scope = 'https://www.googleapis.com/auth/devstorage.full_control';

    private static $last_local_file;

    static function getPath(string $url): ?string{
        $config   = \Mim::$app->config->libUploadGoogleCloud;
        $host     = self::$host . $config->bucket . '/';
        $host_len = strlen($host);

        if(substr($url, 0, $host_len) != $host)
            return null;

        return substr($url, $host_len);
    }

    static function getLocalPath(string $path): ?string{
        $local_path = tempnam(sys_get_temp_dir(), 'mim-lib-upload-google-cloud-');
        $config     = \Mim::$app->config->libUploadGoogleCloud;
        $cert_file  = Keeper::getCertFile();
        $bucket     = $config->bucket;
        $token      = Auth::get($cert_file, self::$scope);

        $object_name= urlencode($path);

        $c_opt      = [
            'url'      => 'https://storage.googleapis.com/storage/v1/b/' . $bucket . '/o/' . $object_name,
            'method'   => 'GET',
            'query'    => [
                'alt'   => 'media'
            ],
            'headers'  => [
                'Authorization' => 'Bearer ' . $token
            ],
            'download' => $local_path
        ];

        $result = Curl::fetch($c_opt);
        if(!$result)
            return self::setError('Unable to reach google cloud storage server');

        self::$last_local_file = $local_path;

        return $local_path;
    }

    static function getLazySizer(string $path, int $width=null, int $height=null, string $compress=null, bool $force=false): ?string{
        return null;
    }

    static function upload(string $local, string $name): ?string{
        if(self::$last_local_file && is_file(self::$last_local_file))
            unlink(self::$last_local_file);

        return Keeper::save((object)[
            'target' => $name,
            'source' => $local,
            'type'   => mime_content_type($local),
            'name'   => basename($name)
        ]);
    }
}