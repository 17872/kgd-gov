<?php

namespace app\models;

interface ProviderIIN
{
    /**
     * Получает данные от провайдеров информации
     * @param string $provider
     * @param float $iin - ИИН
     * @param null $captcha - значение каптчи
     * @return array
     */

    public function getDataIIN(string $provider, float $iin, $captcha = null): array;
}