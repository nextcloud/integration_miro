<?php
/**
 * Nextcloud - Miro
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier
 * @copyright Julien Veyssier 2022
 */

namespace OCA\Miro\Service;

use Datetime;
use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use OCA\Miro\AppInfo\Application;
use OCP\Files\IRootFolder;
use OCP\IConfig;
use OCP\IL10N;
use OCP\IURLGenerator;
use Psr\Log\LoggerInterface;
use OCP\Http\Client\IClientService;
use OCP\Share\IManager as ShareManager;

class MiroAPIService {
	/**
	 * @var string
	 */
	private $appName;
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	/**
	 * @var IL10N
	 */
	private $l10n;
	/**
	 * @var \OCP\Http\Client\IClient
	 */
	private $client;
	/**
	 * @var IConfig
	 */
	private $config;
	/**
	 * @var IRootFolder
	 */
	private $root;
	/**
	 * @var ShareManager
	 */
	private $shareManager;
	/**
	 * @var IURLGenerator
	 */
	private $urlGenerator;

	/**
	 * Service to make requests to Miro API
	 */
	public function __construct (string $appName,
								LoggerInterface $logger,
								IL10N $l10n,
								IConfig $config,
								IRootFolder $root,
								ShareManager $shareManager,
								IURLGenerator $urlGenerator,
								IClientService $clientService) {
		$this->appName = $appName;
		$this->logger = $logger;
		$this->l10n = $l10n;
		$this->client = $clientService->newClient();
		$this->config = $config;
		$this->root = $root;
		$this->shareManager = $shareManager;
		$this->urlGenerator = $urlGenerator;
	}

	/**
	 * @param string $userId
	 * @param string $term
	 * @param int $offset
	 * @param int $limit
	 * @return array
	 * @throws Exception
	 */
	public function searchBoards(string $userId, string $term, int $offset = 0, int $limit = 5): array {
		$params = [
			'include_deleted_channels' => true,
			'is_or_search' => true,
			'page' => 0,
			'per_page' => 60,
			'terms' => $term,
			'time_zone_offset' => 7200,
		];
		$result = $this->request($userId, 'boards/search', $params, 'POST');
		$posts = $result['posts'] ?? [];

		// sort post by creation date, DESC
		usort($posts, function($a, $b) {
			$ta = (int) $a['create_at'];
			$tb = (int) $b['create_at'];
			return ($ta > $tb) ? -1 : 1;
		});

		$posts = array_slice($posts, $offset, $limit);

		return $posts;
	}

	/**
	 * @param string $userId
	 * @param string $miroUserId
	 * @return array
	 * @throws Exception
	 */
	public function getUserAvatar(string $userId, string $miroUserId): array {
		$image = $this->request($userId, 'users/' . $miroUserId . '/image', [], 'GET', false);
		if (!is_array($image)) {
			return ['avatarContent' => $image];
		}
		$image = $this->request($userId, 'users/' . $miroUserId . '/image/default', [], 'GET', false);
		if (!is_array($image)) {
			return ['avatarContent' => $image];
		}

		$userInfo = $this->request($userId, 'users/' . $miroUserId);
		return ['userInfo' => $userInfo];
	}

	/**
	 * @param string $userId
	 * @param string $teamId
	 * @return array
	 * @throws Exception
	 */
	public function getTeamAvatar(string $userId, string $teamId): array {
		$image = $this->request($userId, 'teams/' . $teamId . '/image', [], 'GET', false);
		if (!is_array($image)) {
			return ['avatarContent' => $image];
		}

		$userInfo = $this->request($userId, 'teams/' . $teamId);
		return ['teamInfo' => $userInfo];
	}

	/**
	 * @param string $userId
	 * @return array|string[]
	 * @throws Exception
	 */
	public function getMyBoards(string $userId): array {
		$result = $this->request($userId, 'boards');
		if (isset($result['error'])) {
			return $result;
		}
		return $result;
	}

	/**
	 * @param string $userId
	 * @param string $endPoint
	 * @param array $params
	 * @param string $method
	 * @param bool $jsonResponse
	 * @return array|mixed|resource|string|string[]
	 * @throws Exception
	 */
	public function request(string $userId, string $endPoint, array $params = [], string $method = 'GET',
							bool $jsonResponse = true) {
		$this->checkTokenExpiration($userId);
		$accessToken = $this->config->getUserValue($userId, Application::APP_ID, 'token');
		try {
			$url = Application::MIRO_API_BASE_URL . '/v2/' . $endPoint;
			$options = [
				'headers' => [
					'Authorization'  => 'Bearer ' . $accessToken,
//					'Content-Type' => 'application/x-www-form-urlencoded',
					'User-Agent'  => Application::INTEGRATION_USER_AGENT,
				],
			];

			if (count($params) > 0) {
				if ($method === 'GET') {
					// manage array parameters
					$paramsContent = '';
					foreach ($params as $key => $value) {
						if (is_array($value)) {
							foreach ($value as $oneArrayValue) {
								$paramsContent .= $key . '[]=' . urlencode($oneArrayValue) . '&';
							}
							unset($params[$key]);
						}
					}
					$paramsContent .= http_build_query($params);

					$url .= '?' . $paramsContent;
				} else {
					$options['json'] = $params;
				}
			}

			if ($method === 'GET') {
				$response = $this->client->get($url, $options);
			} else if ($method === 'POST') {
				$response = $this->client->post($url, $options);
			} else if ($method === 'PUT') {
				$response = $this->client->put($url, $options);
			} else if ($method === 'DELETE') {
				$response = $this->client->delete($url, $options);
			} else {
				return ['error' => $this->l10n->t('Bad HTTP method')];
			}
			$body = $response->getBody();
			$respCode = $response->getStatusCode();

			if ($respCode >= 400) {
				return ['error' => $this->l10n->t('Bad credentials')];
			} else {
				if ($jsonResponse) {
					return json_decode($body, true);
				} else {
					return $body;
				}
			}
		} catch (ServerException | ClientException $e) {
			$this->logger->debug('Miro API error : '.$e->getMessage(), ['app' => Application::APP_ID]);
			return ['error' => $e->getMessage()];
		}
	}

	/**
	 * @param string $userId
	 * @return void
	 * @throws \OCP\PreConditionNotMetException
	 */
	private function checkTokenExpiration(string $userId): void {
		$refreshToken = $this->config->getUserValue($userId, Application::APP_ID, 'refresh_token');
		$expireAt = $this->config->getUserValue($userId, Application::APP_ID, 'token_expires_at');
		if ($refreshToken !== '' && $expireAt !== '') {
			$nowTs = (new Datetime())->getTimestamp();
			$expireAt = (int) $expireAt;
			// if token expires in less than a minute or is already expired
			if ($nowTs > $expireAt - 60) {
				$this->refreshToken($userId);
			}
		}
	}

	/**
	 * @param string $userId
	 * @return bool
	 * @throws \OCP\PreConditionNotMetException
	 */
	private function refreshToken(string $userId): bool {
		$clientID = $this->config->getAppValue(Application::APP_ID, 'client_id');
		$clientSecret = $this->config->getAppValue(Application::APP_ID, 'client_secret');
		$redirect_uri = $this->config->getUserValue($userId, Application::APP_ID, 'redirect_uri');
		$refreshToken = $this->config->getUserValue($userId, Application::APP_ID, 'refresh_token');
		if (!$refreshToken) {
			$this->logger->error('No Miro refresh token found', ['app' => Application::APP_ID]);
			return false;
		}
		$result = $this->requestOAuthAccessToken([
			'client_id' => $clientID,
			'client_secret' => $clientSecret,
			'grant_type' => 'refresh_token',
//			'redirect_uri' => $redirect_uri,
			'refresh_token' => $refreshToken,
		], 'POST');
		if (isset($result['access_token'])) {
			$this->logger->info('Miro access token successfully refreshed', ['app' => Application::APP_ID]);
			$accessToken = $result['access_token'];
			$refreshToken = $result['refresh_token'];
			$this->config->setUserValue($userId, Application::APP_ID, 'token', $accessToken);
			$this->config->setUserValue($userId, Application::APP_ID, 'refresh_token', $refreshToken);
			if (isset($result['expires_in'])) {
				$nowTs = (new Datetime())->getTimestamp();
				$expiresAt = $nowTs + (int) $result['expires_in'];
				$this->config->setUserValue($userId, Application::APP_ID, 'token_expires_at', $expiresAt);
			}
			return true;
		} else {
			// impossible to refresh the token
			$this->logger->error(
				'Token is not valid anymore. Impossible to refresh it. '
					. $result['error'] . ' '
					. $result['error_description'] ?? '[no error description]',
				['app' => Application::APP_ID]
			);
			return false;
		}
	}

	/**
	 * @param array $params
	 * @param string $method
	 * @return array
	 */
	public function requestOAuthAccessToken(array $params = [], string $method = 'GET'): array {
		try {
			$url = Application::MIRO_API_BASE_URL . '/v1/oauth/token';
			$options = [
				'headers' => [
					'User-Agent'  => Application::INTEGRATION_USER_AGENT,
				]
			];

			if (count($params) > 0) {
				if ($method === 'GET') {
					$paramsContent = http_build_query($params);
					$url .= '?' . $paramsContent;
				} else {
					$options['body'] = $params;
				}
			}

			if ($method === 'GET') {
				$response = $this->client->get($url, $options);
			} else if ($method === 'POST') {
				$response = $this->client->post($url, $options);
			} else if ($method === 'PUT') {
				$response = $this->client->put($url, $options);
			} else if ($method === 'DELETE') {
				$response = $this->client->delete($url, $options);
			} else {
				return ['error' => $this->l10n->t('Bad HTTP method')];
			}
			$body = $response->getBody();
			$respCode = $response->getStatusCode();

			if ($respCode >= 400) {
				return ['error' => $this->l10n->t('OAuth access token refused')];
			} else {
				return json_decode($body, true);
			}
		} catch (Exception $e) {
			$this->logger->warning('Miro OAuth error : '.$e->getMessage(), ['app' => Application::APP_ID]);
			return ['error' => $e->getMessage()];
		}
	}
}
