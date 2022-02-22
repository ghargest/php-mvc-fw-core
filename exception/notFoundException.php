<?php

namespace ghargest\phpmvc\exception;

use Exception;

class NotFoundException extends \Exception {

    protected $message = "Page not found";
    protected $code = 404;
}