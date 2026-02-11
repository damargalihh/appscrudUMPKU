import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('hotspotUsersTable', (initialUsers = []) => ({
	users: Array.isArray(initialUsers) ? initialUsers : [],
	search: '',
	filter: 'all',
	profileFilter: 'all',
	selected: [],
	refreshTimer: null,
	start() {
		this.refreshUsers();
		this.refreshTimer = setInterval(() => this.refreshUsers(), 5000);
	},
	async refreshUsers() {
		try {
			const response = await window.axios.get('/api/hotspot-users');
			if (Array.isArray(response.data)) {
				this.users = response.data;
				// Remove selected ids that no longer exist
				const existingIds = new Set(response.data.map(u => u.id));
				this.selected = this.selected.filter(id => existingIds.has(id));
			}
		} catch (error) {
			// Silent fail to avoid breaking UI when API is temporarily unavailable.
		}
	},
	toggleSelect(id) {
		const idx = this.selected.indexOf(id);
		if (idx === -1) {
			this.selected.push(id);
		} else {
			this.selected.splice(idx, 1);
		}
	},
	toggleSelectAll() {
		const visible = this.filteredUsers();
		const allIds = visible.map(u => u.id);
		if (this.allSelected()) {
			this.selected = this.selected.filter(id => !allIds.includes(id));
		} else {
			const set = new Set([...this.selected, ...allIds]);
			this.selected = [...set];
		}
	},
	allSelected() {
		const visible = this.filteredUsers();
		if (visible.length === 0) return false;
		return visible.every(u => this.selected.includes(u.id));
	},
	filteredUsers() {
		const searchText = this.search.toLowerCase().trim();
		return this.users.filter((user) => {
			const name = (user.name || '').toLowerCase();
			const profile = (user.profile || '').toLowerCase();
			const matchesSearch = !searchText || name.includes(searchText) || profile.includes(searchText);
			const matchesStatus = this.filter === 'all'
				|| (this.filter === 'active' && !user.disabled)
				|| (this.filter === 'disabled' && user.disabled);
			const matchesProfile = this.profileFilter === 'all' || profile === this.profileFilter;
			return matchesSearch && matchesStatus && matchesProfile;
		});
	},
}));

Alpine.start();
