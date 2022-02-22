<?php

namespace ghargest\phpmvc\middlewares;

use ghargest\phpmvc\Application;
use ghargest\phpmvc\exception\ForbiddenException;

class AuthMiddleware extends BaseMiddleware {

    public array $actions = [];

    public function __construct(array $actions = []) {
    
        $this->actions = $actions;
    }

    public function execute() {

        if (Application::$app->isGuest()) {
            if (empty($this->actions) || in_array(Application::$app->controller->action, $this->actions)) {
                throw new ForbiddenException();
            }
        }
    }    
}