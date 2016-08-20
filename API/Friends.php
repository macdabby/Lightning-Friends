<?php

namespace Modules\Friends\API;

use Lightning\Tools\Database;
use Lightning\Tools\Output;
use Lightning\Tools\Request;
use Lightning\View\API;
use Lightning\Tools\ClientUser;
use Modules\Friends\Model\Friendship;

class Friends extends API {

    public function hasAccess() {
        return ClientUser::requireLogin();
    }

    /**
     * Get list of confirmed friends.
     */
    public function get() {
        $friends = Friendship::getConfirmed(ClientUser::getInstance()->id) ?: [];
        $friends = Database::getInstance()->selectIndexed('user', 'user_id', ['user_id' => ['IN', $friends]], ['first', 'last', 'user_id']);

        return ['friends' => !empty($friends) ? $friends : []];
    }

    /**
     * Get list of pending requests.
     */
    public function getPending() {
        $friends = Database::getInstance()->selectColumn(
            [
                'from' => ['f1' => 'friendship'],
                'join' => [
                    'LEFT JOIN',
                    ['f2' => 'friendship'],
                    'ON f1.friend2 = f2.friend1 and f1.friend1 = f2.friend2'
                ]
            ],
            'f1.friend2',
            [
                'f1.friend1' => ClientUser::getInstance()->id,
                'f2.friend1' => null,
            ]
        );

        return ['pending' => !empty($friends) ? $friends : []];
    }

    /**
     * Get list of people requesting your friendship.
     */
    public function getRequests() {
        $friends = Database::getInstance()->selectColumn(
            [
                'from' => ['f1' => 'friendship'],
                'join' => [
                    'LEFT JOIN',
                    ['f2' => 'friendship'],
                    'ON f1.friend2 = f2.friend1 and f1.friend1 = f2.friend2'
                ]
            ],
            'f1.friend1',
            [
                'f1.friend2' => ClientUser::getInstance()->id,
                'f2.friend1' => null,
            ]
        );

        return ['requests' => !empty($friends) ? $friends : []];
    }

    /**
     * Send a request.
     */
    public function post() {
        if (!$friend_id = Request::post('user_id', 'int')) {
            Output::error('Missing User ID');
        }

        Database::getInstance()->insert('friendship', [
            'friend1' => ClientUser::getInstance()->id,
            'friend2' => $friend_id,
            'time' => time(),
        ], true);
    }

    /**
     * Unfriend someone.
     */
    public function postRemove() {
        if (!$friend_id = Request::post('user_id', 'int')) {
            Output::error('Missing User ID');
        }

        Database::getInstance()->delete('friendship', [
            'friend1' => ClientUser::getInstance()->id,
            'friend2' => $friend_id,
        ]);
        Database::getInstance()->delete('friendship', [
            'friend2' => ClientUser::getInstance()->id,
            'friend1' => $friend_id,
        ]);
    }
}
