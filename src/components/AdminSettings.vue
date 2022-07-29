<template>
	<div id="miro_prefs" class="section">
		<h2>
			<MiroIcon class="miro-icon" />
			{{ t('integration_miro', 'Miro integration') }}
		</h2>
		<p class="settings-hint">
			{{ t('integration_miro', 'If you want to allow your Nextcloud users to connect to Miro via OAuth, create an application in Miro and set the ID and secret here.') }}
			<a class="external" href="https://developers.miro.com/docs/getting-started-with-oauth#prerequisites">How to create a Miro OAuth app</a>
		</p>
		<br>
		<p class="settings-hint">
			<InformationVariantIcon :size="24" class="icon" />
			{{ t('integration_miro', 'Make sure you set the "Redirect URI" to') }}
			&nbsp;<b> {{ redirect_uri }} </b>
		</p>
		<p class="settings-hint">
			<InformationVariantIcon :size="24" class="icon" />
			{{ t('integration_miro', 'Make sure you enable the "Expire user authorization token" app setting. This is more secure and is mandatory to use this integration.') }}
		</p>
		<br>
		<p class="settings-hint">
			{{ t('integration_miro', 'Put the "Application ID" and "Application secret" below. Your Nextcloud users will then see a "Connect to Miro" button in their personal settings.') }}
		</p>
		<div class="field">
			<label for="miro-client-id">
				<KeyIcon :size="20" class="icon" />
				{{ t('integration_miro', 'Application ID') }}
			</label>
			<input id="miro-client-id"
				v-model="state.client_id"
				type="password"
				:readonly="readonly"
				:placeholder="t('integration_miro', 'ID of your Miro application')"
				@input="onInput"
				@focus="readonly = false">
		</div>
		<div class="field">
			<label for="miro-client-secret">
				<KeyIcon :size="20" class="icon" />
				{{ t('integration_miro', 'Application secret') }}
			</label>
			<input id="miro-client-secret"
				v-model="state.client_secret"
				type="password"
				:readonly="readonly"
				:placeholder="t('integration_miro', 'Client secret of your Miro application')"
				@focus="readonly = false"
				@input="onInput">
		</div>
		<CheckboxRadioSwitch
			class="field"
			:checked.sync="state.use_popup"
			@update:checked="onUsePopupChanged">
			{{ t('integration_miro', 'Use a popup to authenticate') }}
		</CheckboxRadioSwitch>
		<CheckboxRadioSwitch
			class="field"
			:checked.sync="state.override_link_click"
			@update:checked="onOverrideChanged">
			{{ t('integration_miro', 'Open Miro board links in Nextcloud') }}
		</CheckboxRadioSwitch>
	</div>
</template>

<script>
import { loadState } from '@nextcloud/initial-state'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { delay } from '../utils'
import { showSuccess, showError } from '@nextcloud/dialogs'
import CheckboxRadioSwitch from '@nextcloud/vue/dist/Components/CheckboxRadioSwitch'
import MiroIcon from './icons/MiroIcon'
import InformationVariantIcon from 'vue-material-design-icons/InformationVariant'
import KeyIcon from 'vue-material-design-icons/Key'

export default {
	name: 'AdminSettings',

	components: {
		MiroIcon,
		CheckboxRadioSwitch,
		InformationVariantIcon,
		KeyIcon,
	},

	props: [],

	data() {
		return {
			state: loadState('integration_miro', 'admin-config'),
			// to prevent some browsers to fill fields with remembered passwords
			readonly: true,
			redirect_uri: window.location.protocol + '//' + window.location.host + generateUrl('/apps/integration_miro/oauth-redirect'),
		}
	},

	watch: {
	},

	mounted() {
	},

	methods: {
		onUsePopupChanged(newValue) {
			this.saveOptions({ use_popup: newValue ? '1' : '0' })
		},
		onOverrideChanged(newValue) {
			this.saveOptions({ override_link_click: newValue ? '1' : '0' })
		},
		onInput() {
			delay(() => {
				this.saveOptions({
					client_id: this.state.client_id,
					client_secret: this.state.client_secret,
				})
			}, 2000)()
		},
		saveOptions(values) {
			const req = {
				values,
			}
			const url = generateUrl('/apps/integration_miro/admin-config')
			axios.put(url, req).then((response) => {
				showSuccess(t('integration_miro', 'Miro admin options saved'))
			}).catch((error) => {
				showError(
					t('integration_miro', 'Failed to save Miro admin options')
					+ ': ' + (error.response?.request?.responseText ?? '')
				)
				console.error(error)
			})
		},
	},
}
</script>

<style scoped lang="scss">
#miro_prefs {
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

	h2 {
		display: flex;
		.miro-icon {
			margin-right: 12px;
		}
	}
}
</style>
