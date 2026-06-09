<?php

namespace Tests\Feature\Gamification;

use Tests\TestCase;

abstract class GamificationMysqlTestCase extends TestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();

        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql.database', 'claudsoft_platform');

        return $app;
    }
}
