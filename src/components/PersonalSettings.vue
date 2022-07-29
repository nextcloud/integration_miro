<template>
	<div id="miro_prefs" class="section">
		<h2>
			<MiroIcon class="miro-icon" />
			{{ t('integration_miro', 'Miro integration') }}
		</h2>
		<p v-if="!showOAuth && !connected" class="settings-hint">
			{{ t('integration_miro', 'Ask your administrator to configure the Miro integration in Nextcloud.') }}
		</p>
		<div id="miro-content">
			<Button v-if="!connected && showOAuth"
				id="miro-connect"
				class="field"
				:disabled="loading === true"
				:class="{ loading }"
				@click="onConnectClick">
				<template #icon>
					<OpenInNewIcon />
				</template>
				{{ t('integration_miro', 'Connect to Miro') }}
			</Button>
			<div v-if="connected" class="field">
				<label class="miro-connected">
					<CheckIcon :size="24" class="icon" />
					{{ t('integration_miro', 'Connected as {user}', { user: connectedDisplayName }) }}
				</label>
				<Button id="miro-rm-cred" @click="onLogoutClick">
					<template #icon>
						<CloseIcon />
					</template>
					{{ t('integration_miro', 'Disconnect from Miro') }}
				</Button>
			</div>
		</div>
	</div>
</template>

<script>
import CheckIcon from 'vue-material-design-icons/Check'
import OpenInNewIcon from 'vue-material-design-icons/OpenInNew'
import CloseIcon from 'vue-material-design-icons/Close'
import Button from '@nextcloud/vue/dist/Components/Button'
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { oauthConnect } from '../utils'
import { showSuccess, showError } from '@nextcloud/dialogs'
import MiroIcon from './icons/MiroIcon'

export default {
	name: 'PersonalSettings',

	components: {
		MiroIcon,
		Button,
		OpenInNewIcon,
		CloseIcon,
		CheckIcon,
	},

	props: [],

	data() {
		return {
			state: loadState('integration_miro', 'user-config'),
			loading: false,
			redirect_uri: window.location.protocol + '//' + window.location.host + generateUrl('/apps/integration_miro/oauth-redirect'),
		}
	},

	computed: {
		showOAuth() {
			return !!this.state.client_id && !!this.state.client_secret
		},
		connected() {
			return !!this.state.token && !!this.state.user_name
		},
		connectedDisplayName() {
			return this.state.user_name
		},
	},

	watch: {
	},

	mounted() {
		const paramString = window.location.search.substr(1)
		// eslint-disable-next-line
		const urlParams = new URLSearchParams(paramString)
		const glToken = urlParams.get('miroToken')
		if (glToken === 'success') {
			showSuccess(t('integration_miro', 'Successfully connected to Miro!'))
		} else if (glToken === 'error') {
			showError(t('integration_miro', 'Error connecting to Miro:') + ' ' + urlParams.get('message'))
		}
	},

	methods: {
		onLogoutClick() {
			this.state.token = ''
			this.saveOptions({ token: '' })
		},
		saveOptions(values) {
			const req = {
				values,
			}
			const url = generateUrl('/apps/integration_miro/config')
			axios.put(url, req).then((response) => {
				if (values.token === '' && response.data.user_name === '') {
					showSuccess(t('integration_miro', 'Successfully disconnected'))
				} else {
					showSuccess(t('integration_miro', 'Miro options saved'))
				}
			}).catch((error) => {
				showError(
					t('integration_miro', 'Failed to save Miro options')
					+ ': ' + (error.response?.request?.responseText ?? '')
				)
				console.error(error)
			}).then(() => {
				this.loading = false
			})
		},
		onConnectClick() {
			if (this.showOAuth) {
				this.connectWithOauth()
			}
		},
		connectWithOauth() {
			if (this.state.use_popup) {
				oauthConnect(this.state.client_id, null, true)
					.then((data) => {
						this.state.token = 'dummyToken'
						this.state.user_name = data.userName
						this.state.user_id = data.userId
					})
			} else {
				oauthConnect(this.state.client_id, 'settings')
			}
		},
	},
}
</script>

<style scoped lang="scss">
#miro_prefs {
	h2 {
		display: flex;

		.miro-icon {
			margin-right: 12px;
		}
	}

	.field {
		display: flex;
		align-items: center;
		margin-left: 30px;

		input,
		label {
			width: 300px;
		}

		label {
			display: flex;
			align-items: center;
		}

		.icon {
			margin-right: 8px;
		}
	}

	.settings-hint {
		display: flex;
		align-items: center;
	}
}
</style>
