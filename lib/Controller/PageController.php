<?php
/**
 * Nextcloud - Miro integration
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2022
 */

namespace OCA\Miro\Controller;

use OCA\Miro\AppInfo\Application;
use OCA\Miro\Service\MiroAPIService;
use OCP\App\IAppManager;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\IRequest;
use OCP\Security\ICrypto;

class PageController extends Controller {

	public function __construct(
		string $appName,
		IRequest $request,
		private IConfig $config,
		private IAppManager $appManager,
		private IInitialState $initialStateService,
		private MiroAPIService $miroAPIService,
		private ICrypto $crypto,
		private ?string $userId,
	) {
		parent::__construct($appName, $request);
	}

	/**
	 * @return TemplateResponse
	 * @throws \Exception
	 */
	#[NoAdminRequired]
	#[NoCSRFRequired]
	public function index(): TemplateResponse {
		$token = $this->config->getUserValue($this->userId, Application::APP_ID, 'token');
		$clientID = $this->config->getAppValue(Application::APP_ID, 'client_id');
		$clientID = $clientID === '' ? '' : $this->crypto->decrypt($clientID);
		$clientSecret = $this->config->getAppValue(Application::APP_ID, 'client_secret');
		$usePopup = $this->config->getAppValue(Application::APP_ID, 'use_popup', '0') === '1';

		$miroUserId = $this->config->getUserValue($this->userId, Application::APP_ID, 'user_id');
		$miroUserName = $this->config->getUserValue($this->userId, Application::APP_ID, 'user_name');
		$miroTeamId = $this->config->getUserValue($this->userId, Application::APP_ID, 'team_id');
		$miroTeamName = $this->config->getUserValue($this->userId, Application::APP_ID, 'team_name');

		$talkEnabled = $this->appManager->isEnabledForUser('spreed');

		$pageInitialState = [
			'token' => $token ? 'dummyTokenContent' : '',
			'client_id' => $clientID,
			// don't expose the client secret to users
			'client_secret' => $clientSecret !== '',
			'use_popup' => $usePopup,
			'user_id' => $miroUserId,
			'user_name' => $miroUserName,
			'team_id' => $miroTeamId,
			'team_name' => $miroTeamName,
			'talk_enabled' => $talkEnabled,
			'board_list' => [],
		];
		if ($token !== '') {
			$boards = $this->miroAPIService->getMyBoards($this->userId);
			if (isset($boards['error'])) {
				$pageInitialState['board_list_error'] = $boards['error'];
			} else {
				$pageInitialState['board_list'] = $boards;
			}
		}
		$this->initialStateService->provideInitialState('miro-state', $pageInitialState);
		return new TemplateResponse(Application::APP_ID, 'main', []);
	}
}
