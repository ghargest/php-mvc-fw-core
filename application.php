<?php

namespace ghargest\phpmvc;

use ghargest\phpmvc\db\Database;

class Application {

    public static string $ROOT_DIR;

    public string $layout = 'main';
    public string $userClass;
    public View $view;
    public Request $request;
    public Response $response;
    public Router $router;
    public Session $session;
    public Database $db;
    public static Application $app;
    public ?UserModel $user;
    public ?Controller $controller = null;

    public function __construct($rootPath, array $config) {
        
        self::$ROOT_DIR = $rootPath;
        $this->userClass = $config['userClass'];
        $this->view = new View();
        $this->request = new Request();
        $this->response = new Response();
        $this->router = new Router($this->request, $this->response);
        $this->session = new Session();
        $this->db = new Database($config['db']);
        self::$app = $this;

        $primaryValue = $this->session->get('user');

        if ($primaryValue) {
            $primaryKey = $this->userClass::primaryKey();
            $this->user = $this->userClass::findOne('users', [$primaryKey => $primaryValue]);
        } else {
            $this->user = null;
        }
    }

    public function run() {

        try {
            echo $this->router->resolve();
        } catch(\Exception $e) {
            $this->response->setStatusCode($e->getCode());
            echo $this->view->renderView('_error', [
                'exception' => $e
            ]);
        }
    }

    public function login(UserModel $user) {

        $this->user = $user;
        $primaryKey = $user::primaryKey();
        $primaryValue = $user->{$primaryKey};
        $this->session->set('user', $primaryValue);
        return true;
    }

    public function logout() {

        $this->user = null;
        $this->session->remove('user');
    }

    public static function isGuest() {

        return !self::$app->user;
    }
}