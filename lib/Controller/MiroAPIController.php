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

use OCA\Miro\Service\MiroAPIService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\AppFramework\Http\RedirectResponse;
use OCP\IRequest;
use OCP\IURLGenerator;

class MiroAPIController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
		private IURLGenerator $urlGenerator,
		private MiroAPIService $miroAPIService,
		private ?string $userId
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * get miro user avatar
	 *
	 * @param string $userId
	 * @param int $useFallback
	 * @return DataDisplayResponse|RedirectResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function getUserAvatar(string $userId, int $useFallback = 1) {
		$result = $this->miroAPIService->getUserAvatar($this->userId, $userId);
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
	 *
	 * @param string $teamId
	 * @param int $useFallback
	 * @return DataDisplayResponse|RedirectResponse
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function getTeamAvatar(string $teamId, int $useFallback = 1) {
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
	#[NoAdminRequired]
	public function getBoards(): DataResponse {
		$result = $this->miroAPIService->getMyBoards($this->userId);
		if (isset($result['error'])) {
			return new DataResponse($result, Http::STATUS_BAD_REQUEST);
		} else {
			return new DataResponse($result);
		}
	}

	/**
	 * @param string $name
	 * @param string $description
	 * @param string $teamId
	 * @return DataResponse
	 * @throws \Exception
	 */
	#[NoAdminRequired]
	public function createBoard(string $name, string $description, string $teamId): DataResponse {
		$result = $this->miroAPIService->createBoard($this->userId, $name, $description, $teamId);
		if (isset($result['error'])) {
			return new DataResponse($result, Http::STATUS_BAD_REQUEST);
		} else {
			return new DataResponse($result);
		}
	}

	/**
	 * @param string $id
	 * @return DataResponse
	 * @throws \Exception
	 */
	#[NoAdminRequired]
	public function deleteBoard(string $id): DataResponse {
		$result = $this->miroAPIService->deleteBoard($this->userId, $id);
		if ($result === null) {
			return new DataResponse($result);
		}
		return new DataResponse($result, Http::STATUS_BAD_REQUEST);
	}
}
