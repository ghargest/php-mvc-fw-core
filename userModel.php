<?php

namespace ghargest\phpmvc;

use ghargest\phpmvc\db\DbModel;

abstract class UserModel extends DbModel {

    abstract public function displayName(): string;
}