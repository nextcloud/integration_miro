<?php
/**
 * Nextcloud - Miro
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2022
 */

return [
    'routes' => [
		['name' => 'config#isUserConnected', 'url' => '/is-connected', 'verb' => 'GET'],
		['name' => 'config#oauthRedirect', 'url' => '/oauth-redirect', 'verb' => 'GET'],
        ['name' => 'config#setConfig', 'url' => '/config', 'verb' => 'PUT'],
        ['name' => 'config#setAdminConfig', 'url' => '/admin-config', 'verb' => 'PUT'],
		['name' => 'config#popupSuccessPage', 'url' => '/popup-success', 'verb' => 'GET'],

		['name' => 'miroAPI#getBoards', 'url' => '/boards', 'verb' => 'GET'],
        ['name' => 'miroAPI#getUserAvatar', 'url' => '/users/{userId}/image', 'verb' => 'GET'],
        ['name' => 'miroAPI#getTeamAvatar', 'url' => '/teams/{teamId}/image', 'verb' => 'GET'],
    ]
];
