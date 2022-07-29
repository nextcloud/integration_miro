<template>
	<Content app-name="integration_miro">
		<MiroNavigation
			:boards="activeBoards"
			:selected-board-id="selectedBoardId"
			:is-configured="connected"
			@create-board-clicked="onCreateBoardClick"
			@board-clicked="onBoardClicked"
			@delete-board="onBoardDeleted" />
		<AppContent
			:list-max-width="50"
			:list-min-width="20"
			:list-size="20"
			:show-details="false"
			@update:showDetails="a = 2">
			<!--template slot="list">
			</template-->
			<BoardDetails v-if="selectedBoard"
				:board="selectedBoard"
				:talk-enabled="state.talk_enabled" />
			<div v-else-if="!connected">
				<EmptyContent>
					<template #icon>
						<CogIcon />
					</template>
					<span class="emptyContentWrapper">
						<span>
							{{ t('integration_miro', 'You are not connected to Miro') }}
						</span>
						<Button
							class="oauthButton"
							@click="connectWithOauth">
							<template #icon>
								<OpenInNewIcon />
							</template>
							{{ t('integration_miro', 'Connect to Miro') }}
						</Button>
					</span>
				</EmptyContent>
			</div>
			<EmptyContent v-else-if="activeBoardCount === 0">
				<template #icon>
					<MiroIcon />
				</template>
				<span class="emptyContentWrapper">
					<span>
						{{ t('integration_miro', 'You haven\'t created any boards yet') }}
					</span>
					<Button
						class="createButton"
						@click="onCreateBoardClick">
						<template #icon>
							<PlusIcon />
						</template>
						{{ t('integration_miro', 'Create a board') }}
					</Button>
				</span>
			</EmptyContent>
			<EmptyContent v-else>
				<template #icon>
					<MiroIcon />
				</template>
				{{ t('integration_miro', 'No selected board') }}
			</EmptyContent>
		</AppContent>
		<Modal v-if="creationModalOpen"
			size="small"
			@close="closeCreationModal">
			<CreationForm
				:loading="creating"
				focus-on-field="name"
				@ok-clicked="onCreationValidate"
				@cancel-clicked="closeCreationModal" />
		</Modal>
	</Content>
</template>

<script>
import OpenInNewIcon from 'vue-material-design-icons/OpenInNew'
import CogIcon from 'vue-material-design-icons/Cog'
import PlusIcon from 'vue-material-design-icons/Plus'
import Button from '@nextcloud/vue/dist/Components/Button'
import AppContent from '@nextcloud/vue/dist/Components/AppContent'
import Content from '@nextcloud/vue/dist/Components/Content'
import Modal from '@nextcloud/vue/dist/Components/Modal'
import EmptyContent from '@nextcloud/vue/dist/Components/EmptyContent'

import { generateUrl } from '@nextcloud/router'
import { loadState } from '@nextcloud/initial-state'
import axios from '@nextcloud/axios'
import { showSuccess, showError, showUndo } from '@nextcloud/dialogs'

import MiroNavigation from './components/MiroNavigation'
import CreationForm from './components/CreationForm'
import BoardDetails from './components/BoardDetails'
import MiroIcon from './components/icons/MiroIcon'
import { oauthConnect, Timer } from './utils'

export default {
	name: 'App',

	components: {
		MiroIcon,
		CreationForm,
		BoardDetails,
		MiroNavigation,
		CogIcon,
		PlusIcon,
		OpenInNewIcon,
		AppContent,
		Content,
		Modal,
		EmptyContent,
		Button,
	},

	props: {
	},

	data() {
		return {
			creationModalOpen: false,
			selectedBoardId: '',
			state: loadState('integration_miro', 'miro-state'),
			configureUrl: generateUrl('/settings/user/connected-accounts'),
			creating: false,
		}
	},

	computed: {
		connected() {
			return !!this.state.user_name && !!this.state.token
		},
		activeBoards() {
			return this.state.board_list.filter((b) => !b.trash)
		},
		activeBoardsById() {
			return this.activeBoards.reduce((object, item) => {
				object[item.id] = item
				return object
			}, {})
		},
		activeBoardCount() {
			return this.activeBoards.length
		},
		selectedBoard() {
			return this.selectedBoardId
				? this.activeBoardsById[this.selectedBoardId]
				: null
		},
	},

	watch: {
	},

	beforeMount() {
		console.debug('state', this.state)
	},

	mounted() {
	},

	methods: {
		connectWithOauth() {
			if (this.state.use_popup) {
				oauthConnect(this.state.client_id, null, true)
					.then((data) => {
						this.state.token = 'dummyToken'
						this.state.user_name = data.userName
						this.state.user_id = data.userId
						this.getBoards()
					})
			} else {
				oauthConnect(this.state.client_id, 'app')
			}
		},
		getBoards() {
			const url = generateUrl('/apps/integration_miro/boards')
			axios.get(url).then((response) => {
				this.state.board_list.push(...response.data)
			}).catch((error) => {
				showError(
					t('integration_miro', 'Failed to get boards')
					+ ': ' + (error.response?.data?.error ?? error.response?.request?.responseText ?? '')
				)
				console.debug(error)
			}).then(() => {
			})
		},
		onCreateBoardClick() {
			this.creationModalOpen = true
		},
		closeCreationModal() {
			this.creationModalOpen = false
		},
		onCreationValidate(board) {
			this.creating = true
			board.trash = false
			const req = {
				name: board.name,
				password: board.password,
			}
			const url = generateUrl('/apps/integration_miro/board')
			axios.post(url, req).then((response) => {
				showSuccess(t('integration_miro', 'New board was created in Miro'))
				board.id = response.data?.id
				this.state.board_list.push(board)
				this.selectedBoardId = board.id
				this.creationModalOpen = false
			}).catch((error) => {
				showError(
					t('integration_miro', 'Failed to create new board')
					+ ': ' + (error.response?.data?.error ?? error.response?.request?.responseText ?? '')
				)
				console.debug(error)
			}).then(() => {
				this.creating = false
			})
		},
		onBoardClicked(boardId) {
			console.debug('select board', boardId)
			this.selectedBoardId = boardId
		},
		deleteBoard(boardId) {
			console.debug('DELETE board', boardId)
			const url = generateUrl('/apps/integration_miro/board/{boardId}', { boardId })
			axios.delete(url).then((response) => {
			}).catch((error) => {
				showError(
					t('integration_miro', 'Failed to delete the board')
					+ ': ' + (error.response?.data?.error ?? error.response?.request?.responseText ?? '')
				)
				console.debug(error)
			})
		},
		onBoardDeleted(boardId) {
			// deselect the board
			if (boardId === this.selectedBoardId) {
				this.selectedBoardId = ''
			}

			// hide the board nav item
			const boardIndex = this.state.board_list.findIndex((b) => b.id === boardId)
			const board = this.state.board_list[boardIndex]
			if (boardIndex !== -1) {
				board.trash = true
			}

			// cancel or delete
			const deletionTimer = new Timer(() => {
				this.deleteBoard(boardId)
			}, 10000)
			showUndo(
				t('integration_miro', '{name} deleted', { name: board.name }),
				() => {
					deletionTimer.pause()
					board.trash = false
				},
				{ timeout: 10000 }
			)
		},
	},
}
</script>

<style scoped lang="scss">
// TODO in global css loaded by main
body {
	min-height: 100%;
	height: auto;
}

.settings {
	display: flex;
	flex-direction: column;
	align-items: center;
}

.emptyContentWrapper {
	display: flex;
	flex-direction: column;
	align-items: center;
}

.createButton,
.oauthButton {
	margin-top: 12px;
}
</style>
