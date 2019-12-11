<?php

namespace app\models;

use yii\httpclient\Client;

class ProviderDataINN implements ProviderIIN
{
    public $clientKey = '66f5fa58da14ec11bf8082004e0a82e4';

    public function getDataIIN(string $provider, float $iin, $captcha = null): array
    {
        $Provider = 'app\models\ProvidersIIN\\' . $provider;

        $Provider = new $Provider;

        $Client = new Client();

        $ProviderData = $Provider->getData($Client, $iin);

        $ProvidersAr = [];

        if ( $ProviderData['captcha'] ) {

            $ProviderData = $Provider->getData($Client, $iin, $ProviderData['captcha'], $ProviderData);

            if ($ProviderData['content']) {
                $ProvidersAr = $Provider->Response(['content' => $ProviderData['content']]);
            }
        }

        return $ProvidersAr;
    }
}