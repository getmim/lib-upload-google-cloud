<?php
/**
 * Keeper
 * @package lib-upload-google-cloud
 * @version 0.0.1
 */

namespace LibUploadGoogleCloud\Library;

use LibGoogleCloud\Library\Auth;
use LibCurl\Library\Curl;

class Keeper implements \LibUpload\Iface\Keeper
{
    private static $last_error;

    private static $host  = 'https://storage.googleapis.com/';
    private static $scope = 'https://www.googleapis.com/auth/devstorage.full_control';

    private static function setError(string $error){
        self::$error = $error;
        return null;
    }

    static function getId(string $file): ?string{
        $config   = \Mim::$app->config->libUploadGoogleCloud;
        $host     = self::$host . $config->bucket . '/';
        $host_len = strlen($host);

        if(substr($file, 0, $host_len) != $host)
            return null;

        return substr($file, $host_len);
    }

    static function getCertFile(): string{
        $config   = \Mim::$app->config->libUploadGoogleCloud;
        $cert_file= $config->cert_file;
        if(substr($cert_file,0,1) != '/')
            $cert_file = realpath(BASEPATH . '/' . $cert_file);

        return $cert_file;
    }

    static function lastError(): ?string{
        return self::$last_error;
    }

    static function save(object $file): ?string{
        $config    = \Mim::$app->config->libUploadGoogleCloud;
        $cert_file = self::getCertFile();
        $bucket    = $config->bucket;

        $token = Auth::get($cert_file, self::$scope);

        $c_opt = [
            'url'     => 'https://storage.googleapis.com/upload/storage/v1/b/' . $bucket . '/o',
            'method'  => 'POST',
            'headers' => [
                'Content-Type'  => 'multipart/related',
                'Authorization' => 'Bearer ' . $token
            ],
            'content' => [
                'meta' => [
                    'headers' => [
                        'Content-Type' => 'application/json; charset=UTF-8'
                    ],
                    'content' => [
                        'name' => $file->target
                    ]
                ],
                'file' => new \CURLFile($file->source, $file->type, $file->name)
            ],
            'query' => [
                'uploadType' => 'multipart',
                'predefinedAcl' => 'publicRead'
            ]
        ];

        $result = Curl::fetch($c_opt);
        if(!$result)
            return self::setError('Unable to reach google cloud storage server');

        if(isset($result->error))
            return self::setError($result->error->message);

        return self::$host . $bucket . '/' . $result->name;
    }
}