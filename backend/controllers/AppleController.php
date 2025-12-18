<?php

namespace backend\controllers;

use common\models\Apple;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * AppleController implements the CRUD actions for Apple model.
 */
class AppleController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'generate' => ['POST'],
                    'fall' => ['POST'],
                    'eat' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Apple models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Apple::find(),
            'sort' => [
                'defaultOrder' => ['id' => SORT_DESC],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Apple model.
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Generates random apples.
     *
     * @return Response
     */
    public function actionGenerate()
    {
        $count = Yii::$app->request->post('count', rand(1, 10));
        $count = max(1, min(100, (int)$count));

        $created = Apple::generateRandom($count);

        Yii::$app->session->setFlash('success', "Generated {$created} new apple(s).");

        return $this->redirect(['index']);
    }

    /**
     * Makes an apple fall from tree.
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionFall($id)
    {
        $model = $this->findModel($id);

        try {
            $model->fallToGround();
            Yii::$app->session->setFlash('success', 'Apple has fallen from the tree.');
        } catch (\yii\base\InvalidCallException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    /**
     * Eats a portion of an apple.
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionEat($id)
    {
        $model = $this->findModel($id);
        $percent = Yii::$app->request->post('percent', 25);
        $percent = max(1, min(100, (float)$percent));

        try {
            $deleted = $model->eat($percent);
            if ($deleted) {
                Yii::$app->session->setFlash('success', 'Apple was fully eaten and removed.');
            } else {
                Yii::$app->session->setFlash('success', "Ate {$percent}% of the apple. Remaining size: {$model->size}");
            }
        } catch (\yii\base\InvalidCallException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        } catch (\InvalidArgumentException $e) {
            Yii::$app->session->setFlash('error', $e->getMessage());
        }

        return $this->redirect(Yii::$app->request->referrer ?: ['index']);
    }

    /**
     * Deletes an existing Apple model.
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        Yii::$app->session->setFlash('success', 'Apple deleted successfully.');

        return $this->redirect(['index']);
    }

    /**
     * Finds the Apple model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     * @return Apple the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Apple::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested apple does not exist.');
    }
}
