<?php
/**
 * Nextcloud - Miro
 *
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2022
 */

namespace OCA\Miro\AppInfo;

use OCA\Miro\Listener\AddContentSecurityPolicyListener;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;

use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\Security\CSP\AddContentSecurityPolicyEvent;
use OCP\Util;

/**
 * Class Application
 *
 * @package OCA\Miro\AppInfo
 */
class Application extends App implements IBootstrap {
	public const APP_ID = 'integration_miro';

	public const INTEGRATION_USER_AGENT = 'Nextcloud Miro integration';
	public const MIRO_API_BASE_URL = 'https://api.miro.com';
	public const MIRO_DOMAIN = 'https://miro.com';
	public const MIRO_SUBDOMAINS = 'https://*.miro.com';

	/**
	 * Constructor
	 *
	 * @param array $urlParams
	 */
	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);
	}

	public function register(IRegistrationContext $context): void {
		$context->registerEventListener(AddContentSecurityPolicyEvent::class, AddContentSecurityPolicyListener::class);
	}

	public function boot(IBootContext $context): void {
		$context->injectFn(function (
			IInitialState $initialState,
			IConfig $config,
			?string $userId
		) {
			$overrideClick = $config->getAppValue(Application::APP_ID, 'override_link_click', '0') === '1';

			// TODO why not making it use-specific?
			$initialState->provideInitialState('override_link_click', $overrideClick);
			Util::addScript(self::APP_ID, self::APP_ID . '-standalone');
		});
	}
}

