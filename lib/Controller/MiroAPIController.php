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

namespace OCA\Miro\Controller;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IConfig;
use OCP\IRequest;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Controller;

use OCA\Miro\Service\MiroAPIService;
use OCA\Miro\AppInfo\Application;
use OCP\IURLGenerator;

class MiroAPIController extends Controller {

	/**
	 * @var IConfig
	 */
	private $config;
	/**
	 * @var MiroAPIService
	 */
	private $miroAPIService;
	/**
	 * @var string|null
	 */
	private $userId;
	/**
	 * @var string
	 */
	private $accessToken;
	/**
	 * @var IURLGenerator
	 */
	private $urlGenerator;

	public function __construct(string $appName,
								IRequest $request,
								IConfig $config,
								IURLGenerator $urlGenerator,
								MiroAPIService $miroAPIService,
								?string $userId) {
		parent::__construct($appName, $request);
		$this->config = $config;
		$this->miroAPIService = $miroAPIService;
		$this->userId = $userId;
		$this->accessToken = $this->config->getUserValue($this->userId, Application::APP_ID, 'token');
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * get miro user avatar
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $userId
	 * @param int $useFallback
	 * @return DataDisplayResponse|RedirectResponse
	 */
	public function getUserAvatar(string $userId, int $useFallback = 1) {
		$result = $this->miroAPIService->getUserAvatar($this->userId, $userId, $this->miroUrl);
		if (isset($result['avatarContent'])) {
			$response = new DataDisplayResponse($result['avatarContent']);
			$response->cacheFor(60 * 60 * 24);
			return $response;
		} elseif ($useFallback !== 0 && isset($result['userInfo'])) {
			$userName = $result['userInfo']['username'] ?? '??';
			$fallbackAvatarUrl = $this->urlGenerator->linkToRouteAbsolute('core.GuestAvatar.getAvatar', ['guestName' => $userName, 'size' => 44]);
			return new RedirectResponse($fallbackAvatarUrl);
		}
		return new DataDisplayResponse('', Http::STATUS_NOT_FOUND);
	}

	/**
	 * get miro team icon/avatar
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param string $teamId
	 * @param int $useFallback
	 * @return DataDisplayResponse|RedirectResponse
	 */
	public function getTeamAvatar(string $teamId, int $useFallback = 1)	{
		$result = $this->miroAPIService->getTeamAvatar($this->userId, $teamId, $this->miroUrl);
		if (isset($result['avatarContent'])) {
			$response = new DataDisplayResponse($result['avatarContent']);
			$response->cacheFor(60 * 60 * 24);
			return $response;
		} elseif ($useFallback !== 0 && isset($result['teamInfo'])) {
			$projectName = $result['teamInfo']['display_name'] ?? '??';
			$fallbackAvatarUrl = $this->urlGenerator->linkToRouteAbsolute('core.GuestAvatar.getAvatar', ['guestName' => $projectName, 'size' => 44]);
			return new RedirectResponse($fallbackAvatarUrl);
		}
		return new DataDisplayResponse('', Http::STATUS_NOT_FOUND);
	}

	/**
	 * @return DataResponse
	 */
	public function getBoards() {
		$result = $this->miroAPIService->getMyBoards($this->userId);
		if (isset($result['error'])) {
			return new DataResponse($result, Http::STATUS_BAD_REQUEST);
		} else {
			return new DataResponse($result);
		}
	}
}
