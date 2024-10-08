import Vue from 'vue'
import App from './App.vue'

import Tooltip from '@nextcloud/vue/dist/Directives/Tooltip.js'
import VueClipboard from 'vue-clipboard2'

Vue.directive('tooltip', Tooltip)
Vue.use(VueClipboard)
Vue.mixin({ methods: { t, n } })

document.addEventListener('DOMContentLoaded', (event) => {
	const View = Vue.extend(App)
	new View().$mount('#content')
})
