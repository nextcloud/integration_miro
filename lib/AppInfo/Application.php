<?php
/**
 * Nextcloud - Miro
 *
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2022
 */

namespace OCA\Miro\AppInfo;

use OCP\IConfig;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;

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
	/**
	 * @var IConfig
	 */
	private $config;

	/**
	 * Constructor
	 *
	 * @param array $urlParams
	 */
	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);

		$container = $this->getContainer();
		/** @var IConfig config */
		$this->config = $container->get(IConfig::class);
	}

	public function register(IRegistrationContext $context): void {
	}

	public function boot(IBootContext $context): void {
//		Util::addStyle(self::APP_ID, 'miro-search');
	}
}

