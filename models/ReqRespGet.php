<?php

namespace app\models;

/**
 * Интерфейс поставщиков информации
 * Interface ReqRespGet
 * @package app\models
 */
interface ReqRespGet
{

    /**
     * Получает данные сервиса
     * @param \yii\httpclient\Client $client
     * @param string $url - url сервиса
     * @param string $method - метод, get или post
     * @param array $data - массив данных для post запроса
     * @param bool $json - передавать ли данные в виде json для REST AP
     * @param array $data - headers заголовки
     * @return object
     */

    public function Request(
        \yii\httpclient\Client $client,
        string $url,
        string $method = 'get',
        array $data = [],
        bool $json = true,
        array $headers = []
    ): \yii\httpclient\Response;

    /**
     * Парсинг данных, возвращаемых сервисом
     * @param array $data - массив данных
     * @return array
     */

    public function Response(array $data): array;

    /**
     * Обрабатывает запрос на получение и вывод данных
     * @param \yii\httpclient\Client $client
     * @param float $iin - ИИН
     * @param null $captcha - значение капчи
     * @param array - массив вспомогательных параметров
     * @return array
     */

    public function getData(\yii\httpclient\Client $client, float $iin, $captcha = null, array $other = []): array;
}