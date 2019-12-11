<?php

namespace app\models;

use app\models\ProvidersIIN\SerjWS;
use Yii;
use yii\base\Model;
use yii\db\Query;

class GetDataIINForm extends Model
{
    public $iin;

    public function rules()
    {
        return [
            [['iin'], 'required'],
            ['iin', 'integer'],
        ];
    }

    public function getRequestIin(float $iin)
    {
        $Provider = new ProviderDataINN();

        $Query = new Query;

        $QData = $Query->select(['data'])
            ->from('iin')
            ->where(['iin' => $iin])
            ->one();

        if (!$QData) {

            $DataResp = $Provider->getDataIIN('SerjWS', $iin);

            if (!empty($DataResp)) {
                Yii::$app->db->createCommand()->insert('iin', [
                    'iin' => $iin,
                    'data' => json_encode($DataResp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ])->execute();

                $DataResp = json_encode($DataResp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }

        } else {
            $DataResp = $QData;
        }

        return $DataResp;
    }
}