// import ClockOutlineIcon from 'vue-material-design-icons/ClockOutline'
// import ShareVariantIcon from 'vue-material-design-icons/ShareVariant'
// import HomeGroupIcon from 'vue-material-design-icons/HomeGroup'
// import AccountGroupIcon from 'vue-material-design-icons/AccountGroup'
// import AccountGroupOutlineIcon from 'vue-material-design-icons/AccountGroupOutline'
import TextIcon from 'vue-material-design-icons/Text'
// import LockIcon from 'vue-material-design-icons/Lock'
// import PaletteIcon from 'vue-material-design-icons/Palette'
// import PaletteSwatchIcon from 'vue-material-design-icons/PaletteSwatch'
// import PaletteSwatchOutlineIcon from 'vue-material-design-icons/PaletteSwatchOutline'
// import TextLongIcon from 'vue-material-design-icons/TextLong'
// import CalendarMonthIcon from 'vue-material-design-icons/CalendarMonth'
// import FormatListBulletedTypeIcon from 'vue-material-design-icons/FormatListBulletedType'

export const fields = {
	name: {
		icon: TextIcon,
		label: t('integration_miro', 'Board name'),
		type: 'text',
		placeholder: t('integration_miro', 'board name'),
		default: t('integration_miro', 'New board'),
		mandatory: true,
	},
	/*
	password: {
		icon: LockIcon,
		label: t('integration_miro', 'Password'),
		type: 'password',
		placeholder: t('integration_miro', 'Password'),
		togglable: true,
		enabled: false,
		view: false,
		default: '',
	},
	comment: {
		icon: TextLongIcon,
		label: t('integration_miro', 'Text area'),
		type: 'textarea',
		placeholder: t('integration_miro', 'long text\nplaceholder\n...'),
		default: 'optional\ndefault\nvalue',
	},
	date: {
		icon: CalendarMonthIcon,
		label: t('integration_miro', 'Date'),
		type: 'ncDate',
		default: new Date('2022-12-29'),
	},
	datetime: {
		icon: ClockOutlineIcon,
		label: t('integration_miro', 'Datetime'),
		type: 'ncDatetime',
		default: new Date('2022-12-29 01:02:03'),
	},
	color: {
		icon: PaletteIcon,
		label: t('integration_miro', 'Color'),
		type: 'ncColor',
		default: '#6ea68f',
	},
	share: {
		icon: ShareVariantIcon,
		label: t('integration_miro', 'Switch'),
		type: 'ncSwitch',
		default: true,
		// enabledIcon: TextIcon,
		// disabledIcon: TextLongIcon,
	},
	share2: {
		label: t('integration_miro', 'Switch without icon'),
		type: 'ncSwitch',
		default: false,
		// enabledIcon: TextIcon,
		// disabledIcon: TextLongIcon,
	},
	check: {
		icon: ShareVariantIcon,
		label: t('integration_miro', 'Checkbox'),
		type: 'ncCheckbox',
		default: true,
		// enabledIcon: TextIcon,
		// disabledIcon: TextLongIcon,
	},
	check2: {
		label: t('integration_miro', 'Checkbox without icon'),
		type: 'ncCheckbox',
		default: false,
		// enabledIcon: TextIcon,
		// disabledIcon: TextLongIcon,
	},
	type: {
		icon: FormatListBulletedTypeIcon,
		label: t('integration_miro', 'Multi select'),
		type: 'select',
		placeholder: t('integration_miro', 'Select a value'),
		options: {
			one: { id: 'one', label: 'First multiselect value', icon: AccountGroupIcon },
			two: { id: 'two', label: 'Second multiselect value', icon: AccountGroupOutlineIcon },
			three: { id: 'three', label: 'Third multiselect long long long value', icon: HomeGroupIcon },
			four: { id: 'four', label: 'Fourth multiselect value without icon' },
		},
		default: { id: 'one', label: 'First multiselect value', icon: AccountGroupIcon },
	},
	style: {
		icon: PaletteSwatchIcon,
		label: t('integration_miro', 'Custom radio selector'),
		type: 'customRadioSet',
		options: {
			one: { label: 'First radio value', icon: PaletteSwatchIcon },
			two: { label: 'Second radio value', icon: PaletteSwatchOutlineIcon },
			three: { label: 'Third radio value with long long long text', icon: PaletteIcon },
			four: { label: 'Fourth radio value without icon' },
		},
		default: 'two',
	},
	permissions: {
		icon: PaletteSwatchIcon,
		label: t('integration_miro', 'Classic Radio selector'),
		type: 'ncRadioSet',
		options: {
			one: { label: 'First radio value', icon: PaletteSwatchIcon },
			two: { label: 'Second radio value', icon: PaletteSwatchOutlineIcon },
			three: { label: 'Third radio value with long text', icon: PaletteIcon },
			four: { label: 'Fourth radio value without icon' },
		},
		default: 'three',
	},
	acls: {
		icon: PaletteSwatchIcon,
		label: t('integration_miro', 'Classic multi checkbox selector'),
		type: 'ncCheckboxSet',
		options: {
			one: { label: 'First radio value', icon: PaletteSwatchIcon },
			two: { label: 'Second radio value', icon: PaletteSwatchOutlineIcon },
			three: { label: 'Third radio value with long text', icon: PaletteIcon },
			four: { label: 'Fourth radio value without icon' },
		},
		default: ['one', 'three'],
	},
	*/
}
