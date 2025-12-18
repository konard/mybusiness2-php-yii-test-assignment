<?php

use common\models\Apple;
use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\Apple $model */

$this->title = 'Apple #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Apples', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="apple-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?php if ($model->status === Apple::STATUS_ON_TREE): ?>
            <?= Html::beginForm(['fall', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
            <?= Html::submitButton('Fall from Tree', ['class' => 'btn btn-warning']) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>

        <?php if ($model->status === Apple::STATUS_FALLEN): ?>
            <?= Html::beginForm(['eat', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
            <div class="input-group d-inline-flex" style="width: auto;">
                <?= Html::textInput('percent', 25, [
                    'class' => 'form-control',
                    'style' => 'width: 80px;',
                    'type' => 'number',
                    'min' => 1,
                    'max' => 100,
                ]) ?>
                <?= Html::submitButton('Eat %', ['class' => 'btn btn-info']) ?>
            </div>
            <?= Html::endForm() ?>
        <?php endif; ?>

        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this apple?',
                'method' => 'post',
            ],
        ]) ?>

        <?= Html::a('Back to List', ['index'], ['class' => 'btn btn-secondary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
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
                        'style' => $style . ' padding: 5px 15px; font-size: 1em;',
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
                    return Html::tag('span', Html::encode($model->statusLabel), [
                        'class' => $class,
                        'style' => 'padding: 5px 15px; font-size: 1em;',
                    ]);
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
                    return '<div class="progress" style="height: 25px; min-width: 200px;">' .
                        '<div class="progress-bar ' . $class . '" role="progressbar" style="width: ' . $percent . '%;">' .
                        number_format($model->size, 2) . ' (' . number_format($percent, 0) . '% remaining)' .
                        '</div></div>';
                },
            ],
            [
                'attribute' => 'eaten_percent',
                'value' => $model->eaten_percent . '%',
            ],
            'created_at:datetime:Appeared on Tree',
            'fallen_at:datetime:Fallen from Tree',
        ],
    ]) ?>

    <?php if ($model->status === Apple::STATUS_FALLEN && $model->fallen_at): ?>
        <?php
        $timeOnGround = time() - $model->fallen_at;
        $rottenTime = Apple::ROTTEN_TIME;
        $timeRemaining = $rottenTime - $timeOnGround;
        ?>
        <div class="alert <?= $timeRemaining <= 0 ? 'alert-danger' : 'alert-info' ?>">
            <?php if ($timeRemaining <= 0): ?>
                <strong>Warning:</strong> This apple should be rotten (has been on ground for <?= round($timeOnGround / 3600, 1) ?> hours).
            <?php else: ?>
                <strong>Info:</strong> This apple has been on the ground for <?= round($timeOnGround / 60, 0) ?> minutes.
                It will become rotten in <?= round($timeRemaining / 60, 0) ?> minutes (<?= round($timeRemaining / 3600, 1) ?> hours).
            <?php endif; ?>
        </div>
    <?php endif; ?>

</div>
