<?php

namespace Modules\Friends\Model;

use Lightning\Tools\Database;

class Friendship {
    public static function getConfirmed($user_id) {
        return Database::getInstance()->selectColumn(
            [
                'from' => ['f1' => 'friendship'],
                'join' => [
                    'JOIN',
                    ['f2' => 'friendship'],
                    'ON f1.friend2 = f2.friend1 and f1.friend1 = f2.friend2'
                ]
            ],
            'f1.friend2',
            [
                'f1.friend1' => $user_id,
            ]
        );
    }
}
