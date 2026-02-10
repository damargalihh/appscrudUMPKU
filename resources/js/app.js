import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('hotspotUsersTable', (initialUsers = []) => ({
	users: Array.isArray(initialUsers) ? initialUsers : [],
	search: '',
	filter: 'all',
	profileFilter: 'all',
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
			}
		} catch (error) {
			// Silent fail to avoid breaking UI when API is temporarily unavailable.
		}
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
