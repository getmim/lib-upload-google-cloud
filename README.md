# lib-upload-google-cloud

## Instalasi

Jalankan perintah di bawah di folder aplikasi:

```
mim app install lib-upload-google-cloud
```

## Konfigurasi

Tambahkan konfigurasi seperti di bawah pada aplikasi:

```php
return [
    'libUploadGoogleCloud' => [
        // google cloud bucket name
        'bucket' => 'my-first-bucket',

        // path to service account credentials json file
        'cert_file' => 'etc/cert/credentials.json'
    ]
];
```