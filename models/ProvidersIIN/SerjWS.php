<?php


namespace app\models\ProvidersIIN;

use app\models\ReqRespGet;
use Treinetic\ImageArtist\lib\Image;
use \app\models\ProviderDataINN;

class SerjWS extends ProviderDataINN implements ReqRespGet
{
    public $Request;

    /**
     * Получает данные с использованием идентификации сессии
     * @param string $url
     * @param string $sessid
     * @param string $method
     * @param array $body
     * @return false|string
     */

    public function RequestHeaders(string $url, string $sessid, string $method = 'POST', array $body = [])
    {
        $headers = array(
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
            'Accept-Encoding: gzip, deflate',
            'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
            'Cookie: PHPSESSID=' . $sessid . '; language=ru',
            'Host: serj.ws',
            'Origin: http://serj.ws',
            'Referer: http://serj.ws/salyk',
            'Upgrade-Insecure-Requests: 1',
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36'
        );

        $body = http_build_query($body);

        $streamOptions = stream_context_create([
            'http' => [
                'method' => $method,
                'header' => $headers,
                'content' => $body
            ],
        ]);

        return file_get_contents($url, 0, $streamOptions);
    }

    public function Request(
        \yii\httpclient\Client $client,
        string $url,
        string $method = 'get',
        array $data = [],
        bool $json = true,
        array $headers = []
    ): \yii\httpclient\Response {
        $response = $client->createRequest()
            ->setMethod($method)
            ->setUrl($url);

        if (!empty($data)) {
            $response->setData($data);
        }

        if ($json) {
            $response->setFormat(\yii\httpclient\Client::FORMAT_JSON);
        }

        if (!empty($headers)) {
            $response->addHeaders($headers);
        }

        $response = $response->send();

        return (!empty($response->isOk) ? $response : []);
    }

    public function Response(array $data): array
    {
        if (empty($data['content'])) {
            return [];
        }

        $ArData = [];

        $document = \phpQuery::newDocumentHTML($data['content']);

        $Data = strip_tags($document->find(".otvetkt")->html());

        $DataResp = preg_split('#([\n\r]+)#Usi', $Data);

        if ($DataResp == 'Некорректный ИИН' || $DataResp == 'Введен неправильный код подтверждения') return [];

        if (!empty($DataResp)) {
            foreach ($DataResp as $key => $value) {
                $Ret = explode(': ', $value);

                if (!empty($Ret[1])) {
                    $ArData[] = [
                        'title' => $Ret[0],
                        'value' => $Ret[1]
                    ];
                }
            }
        }

        return ((!empty($ArData) && count($ArData) > 0) ? $ArData : []);
    }

    public function getData(\yii\httpclient\Client $client, float $iin, $captcha = null, array $other = []): array
    {
        if (empty($iin)) {
            return [];
        }

        if (!empty($captcha) && !empty($other['checksum'])) {

            $ResponseAllCheck = $this->RequestHeaders('http://serj.ws/salyk', $other['sessid'], 'POST', [
                'do' => 'salyk',
                'number' => $iin,
                'code' => $captcha,
                'checksum' => $other['checksum']
            ]);

            if (!empty($ResponseAllCheck)) {
                return ['content' => $ResponseAllCheck];
            } else {
                return [];
            }

            return [];

        }

        if (!empty(($Response = $this->Request($client, 'http://serj.ws/salyk')))) {

            preg_match_all('/src="\/sec.php([^"]*)"/', $Response->content, $matches);

            preg_match_all('/name="checksum"([^"]*)value="(.*)"/', $Response->content, $matchesChecksum);

            $PhpSessId = '';

            if ($Response->headers['set-cookie'][0]) {

                preg_match('/PHPSESSID=(.*);/', $Response->headers['set-cookie'], $matchesSessId);

                $PhpSessId = !empty($matchesSessId[1]) ? $matchesSessId[1] : '';
            }

            $Checksum = end($matchesChecksum);

            if (!empty($matches[1]) && !empty($matches[1][0]) && !empty($matches[1][1]) & !empty($Checksum)) {

                file_put_contents('tmp_img1',
                    $this->RequestHeaders(str_replace('amp;', '', 'http://serj.ws/sec.php' . $matches[1][0]),
                        $PhpSessId, 'GET'));
                file_put_contents('tmp_img2',
                    $this->RequestHeaders(str_replace('amp;', '', 'http://serj.ws/sec.php' . $matches[1][1]),
                        $PhpSessId, 'GET'));

                $img1 = new Image('tmp_img1');
                $img2 = new Image('tmp_img2');

                $img1->merge($img2, $img1->getWidth(), 0);

                if (!empty(($Base64Img = $img1->getDataURI()))) {
                    preg_match('/base64,(.*)/', $Base64Img, $matchesBase64);
                }

                if (!empty($matchesBase64[1])) {

                    if (!empty(($ResponseAntiCaptcha = $this->Request($client,
                        'https://api.anti-captcha.com/createTask', 'post', [
                            'clientKey' => $this->clientKey,
                            'task' => [
                                'type' => 'ImageToTextTask',
                                'body' => $matchesBase64[1],
                                'phrase' => false,
                                'case' => false,
                                'numeric' => false,
                                'math' => 0,
                                'minLength' => 0,
                                'maxLength' => 0
                            ]
                        ])))) {

                        if (!empty($ResponseAntiCaptcha->content)) {
                            $ResponseAntiCaptchaEncode = json_decode($ResponseAntiCaptcha->content,
                                true, 512);

                            if (empty($ResponseAntiCaptchaEncode['errorId']) && !empty($ResponseAntiCaptchaEncode['taskId'])) {

                                while (!empty(($ResponseAntiCaptchaTask = $this->Request($client,
                                    'https://api.anti-captcha.com/getTaskResult', 'post', [
                                        'clientKey' => $this->clientKey,
                                        'taskId' => $ResponseAntiCaptchaEncode['taskId']
                                    ])))) {

                                    $ResponseAntiCaptchaTask = json_decode($ResponseAntiCaptchaTask->content,
                                        true, 512);

                                    if (!empty($ResponseAntiCaptchaTask['errorId']) || !empty($ResponseAntiCaptchaTask['status']) && $ResponseAntiCaptchaTask['status'] == 'ready') {
                                        break;
                                    }

                                }

                                return !empty($ResponseAntiCaptchaTask['solution']['text']) ? [
                                    'iin' => $iin,
                                    'captcha' => $ResponseAntiCaptchaTask['solution']['text'],
                                    'checksum' => $Checksum[0],
                                    'sessid' => $PhpSessId
                                ] : [];

                            }
                        }

                    } else {
                        return [];
                    }
                }
            } else {
                return [];
            }
        }


        return [];
    }
}