<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

Yii::$app->name = 'Проверка налоговой задолженности';

?>
<?php $form = new ActiveForm(); ?>

<form
    id="get_iin"
    action="/"
    method="post"
>

<p v-if="errors.length">
    <b>Пожалуйста исправьте указанные ошибки:</b>
<ul>
    <li v-for="error in errors">{{ error }}</li>
</ul>
</p>

    <input type="hidden" name="<?= Yii::$app->request->csrfParam; ?>" value="<?= Yii::$app->request->csrfToken; ?>" />

<?= $form->field($model, 'iin', [
    'inputOptions' => ['autofocus' => 'autofocus', 'v-model' => 'iin', 'v-filter' => '\'[0-9]\'', 'class' => 'form-control int-form-input']
])->textInput()->input('number', ['placeholder' => "Пример ИИН: 791005350297"])->label('Введите ИИН:') ?>

    <div class="form-group">
        <?= Html::submitButton('Запросить', ['class' => 'btn btn-primary']) ?>
    </div>

    <div>
        <ul>
            <li v-for="item in ar_data" v-if="ar_data.length > 1">
                <b>{{ item.title }}</b>: {{ item.value }}
            </li>
        </ul>
        <h4 v-if="is_empty_data !== null">По запрошеным данным информация не найдена!</h4>
    </div>
</form>

<script type="text/javascript">
    Vue.directive("filter", {
        bind: function(el, binding) {
            this.inputHandler = function(e) {
                var ch = String.fromCharCode(e.which);
                var re = new RegExp(binding.value);
                if (!ch.match(re)) {
                    e.preventDefault();
                }
            };
            el.addEventListener("keypress", this.inputHandler);
        },
        unbind: function(el) {
            el.removeEventListener("keypress", this.inputHandler);
        },
        inputHandler: null
    });

    const app = new Vue({
        el: '#get_iin',
        data: {
            errors: <?= json_encode(( !empty($errors) ? $errors : [] ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>,
            ar_data: <?=  ( !empty($data['data']) && count($data['data'] > 0) ) ? $data['data'] : 'null'; ?>,
            iin: <?= !empty($iin) ? $iin : 'null' ?>,
            is_empty_data: <?= ( !empty($is_post) && empty($data['data']) ) ? true : 'null' ?>
        },
        methods: {
            checkFormIin: function (e) {

                this.iin = this.iin.replace (/\D/g, '');

                this.errors = [];

                if (!this.iin) {
                    this.errors.push('Требуется указать ИИН.');
                }

                e.preventDefault();
            }
        }
    });
</script>
