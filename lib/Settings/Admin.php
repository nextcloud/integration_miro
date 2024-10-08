<?php

namespace OCA\Miro\Settings;

use OCA\Miro\AppInfo\Application;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\Settings\ISettings;

class Admin implements ISettings {

	public function __construct(
		private IConfig $config,
		private IInitialState $initialStateService,
	) {
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		$clientID = $this->config->getAppValue(Application::APP_ID, 'client_id');
		$clientSecret = $this->config->getAppValue(Application::APP_ID, 'client_secret');
		$usePopup = $this->config->getAppValue(Application::APP_ID, 'use_popup', '0') === '1';
		$overrideLinkClick = $this->config->getAppValue(Application::APP_ID, 'override_link_click', '0') === '1';

		$adminConfig = [
			'client_id' => $clientID === '' ? '' : 'dummy-client-id',
			'client_secret' => $clientSecret === '' ? '' : 'dummy-client-secret',
			'use_popup' => $usePopup,
			'override_link_click' => $overrideLinkClick,
		];
		$this->initialStateService->provideInitialState('admin-config', $adminConfig);
		return new TemplateResponse(Application::APP_ID, 'adminSettings');
	}

	public function getSection(): string {
		return 'connected-accounts';
	}

	public function getPriority(): int {
		return 10;
	}
}
