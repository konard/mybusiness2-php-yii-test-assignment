<?php

namespace console\controllers;

use common\models\User;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * User management console commands.
 */
class UserController extends Controller
{
    /**
     * Creates an admin user for testing.
     *
     * @param string $username
     * @param string $email
     * @param string $password
     * @return int
     */
    public function actionCreate($username = 'admin', $email = 'admin@example.com', $password = 'admin123')
    {
        $user = User::findByUsername($username);
        if ($user) {
            $this->stdout("User '{$username}' already exists.\n");
            return ExitCode::OK;
        }

        $user = new User();
        $user->username = $username;
        $user->email = $email;
        $user->setPassword($password);
        $user->generateAuthKey();
        $user->status = User::STATUS_ACTIVE;
        $user->created_at = time();
        $user->updated_at = time();

        if ($user->save()) {
            $this->stdout("User '{$username}' created successfully.\n");
            $this->stdout("Email: {$email}\n");
            $this->stdout("Password: {$password}\n");
            return ExitCode::OK;
        }

        $this->stderr("Failed to create user. Errors:\n");
        foreach ($user->errors as $attribute => $errors) {
            foreach ($errors as $error) {
                $this->stderr("  - {$attribute}: {$error}\n");
            }
        }

        return ExitCode::UNSPECIFIED_ERROR;
    }
}
