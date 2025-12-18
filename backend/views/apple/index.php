<?php

use common\models\Apple;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Apples';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="apple-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Generate New Apples</strong>
        </div>
        <div class="card-body">
            <?php $form = ActiveForm::begin([
                'action' => ['generate'],
                'method' => 'post',
                'options' => ['class' => 'row align-items-end'],
            ]); ?>
            <div class="col-md-4">
                <label for="count" class="form-label">Number of apples (1-100)</label>
                <input type="number" class="form-control" id="count" name="count" value="<?= rand(1, 10) ?>" min="1" max="100">
            </div>
            <div class="col-md-4">
                <?= Html::submitButton('Generate Random Apples', ['class' => 'btn btn-success']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'columns' => [
            'id',
            [
                'attribute' => 'color',
                'format' => 'raw',
                'value' => function (Apple $model) {
                    $colorStyles = [
                        'green' => 'background-color: #4CAF50;',
                        'red' => 'background-color: #f44336;',
                        'yellow' => 'background-color: #FFEB3B; color: #333;',
                        'golden' => 'background-color: #FFD700; color: #333;',
                        'pink' => 'background-color: #E91E63;',
                    ];
                    $style = $colorStyles[$model->color] ?? '';
                    return Html::tag('span', Html::encode($model->color), [
                        'class' => 'badge',
                        'style' => $style . ' padding: 5px 10px;',
                    ]);
                },
            ],
            [
                'attribute' => 'statusLabel',
                'label' => 'Status',
                'format' => 'raw',
                'value' => function (Apple $model) {
                    $statusClasses = [
                        Apple::STATUS_ON_TREE => 'badge bg-success',
                        Apple::STATUS_FALLEN => 'badge bg-warning text-dark',
                        Apple::STATUS_ROTTEN => 'badge bg-danger',
                    ];
                    $model->checkAndUpdateRottenStatus();
                    $class = $statusClasses[$model->status] ?? 'badge bg-secondary';
                    return Html::tag('span', Html::encode($model->statusLabel), ['class' => $class]);
                },
            ],
            [
                'attribute' => 'size',
                'format' => 'raw',
                'value' => function (Apple $model) {
                    $percent = $model->size * 100;
                    $class = 'bg-success';
                    if ($percent < 50) {
                        $class = 'bg-warning';
                    }
                    if ($percent < 25) {
                        $class = 'bg-danger';
                    }
                    return '<div class="progress" style="min-width: 100px;">' .
                        '<div class="progress-bar ' . $class . '" role="progressbar" style="width: ' . $percent . '%;">' .
                        number_format($model->size, 2) .
                        '</div></div>';
                },
            ],
            [
                'attribute' => 'eaten_percent',
                'value' => function (Apple $model) {
                    return $model->eaten_percent . '%';
                },
            ],
            [
                'attribute' => 'created_at',
                'format' => 'datetime',
                'label' => 'Appeared',
            ],
            [
                'attribute' => 'fallen_at',
                'format' => 'datetime',
                'label' => 'Fallen',
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'template' => '{actions}',
                'contentOptions' => ['style' => 'width: 300px;'],
                'buttons' => [
                    'actions' => function ($url, Apple $model) {
                        $model->checkAndUpdateRottenStatus();
                        $buttons = [];

                        // Fall button (only for apples on tree)
                        if ($model->status === Apple::STATUS_ON_TREE) {
                            $buttons[] = Html::beginForm(['fall', 'id' => $model->id], 'post', ['class' => 'd-inline']) .
                                Html::submitButton('Fall', ['class' => 'btn btn-warning btn-sm']) .
                                Html::endForm();
                        }

                        // Eat form (only for fallen apples that are not rotten)
                        if ($model->status === Apple::STATUS_FALLEN) {
                            $buttons[] = Html::beginForm(['eat', 'id' => $model->id], 'post', ['class' => 'd-inline']) .
                                '<div class="input-group input-group-sm d-inline-flex" style="width: auto;">' .
                                Html::textInput('percent', 25, ['class' => 'form-control', 'style' => 'width: 60px;', 'type' => 'number', 'min' => 1, 'max' => 100]) .
                                Html::submitButton('Eat %', ['class' => 'btn btn-info']) .
                                '</div>' .
                                Html::endForm();
                        }

                        // View and Delete buttons
                        $buttons[] = Html::a('View', ['view', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']);
                        $buttons[] = Html::a('Delete', ['delete', 'id' => $model->id], [
                            'class' => 'btn btn-danger btn-sm',
                            'data' => [
                                'confirm' => 'Are you sure you want to delete this apple?',
                                'method' => 'post',
                            ],
                        ]);

                        return implode(' ', $buttons);
                    },
                ],
            ],
        ],
    ]); ?>

</div>
