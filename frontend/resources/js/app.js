import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('hotspotUsersTable', () => ({
	users: [],
	profiles: [],
	search: '',
	filter: 'all',
	profileFilter: 'all',
	selected: [],
	loading: true,
	start() {
		this.loadInitialData();
	},
	async loadInitialData() {
		this.loading = true;
		await Promise.all([this.refreshUsers(), this.refreshProfiles()]);
		this.loading = false;
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
	async refreshProfiles() {
		try {
			const response = await window.axios.get('/api/profiles');
			if (Array.isArray(response.data)) {
				this.profiles = response.data;
			}
		} catch (error) {
			// Silent fail
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

Alpine.data('monitoringUsers', () => ({
	activeUsers: [],
	search: '',
	loading: true,
	currentPage: 1,
	perPage: 10,
	// Filters
	subnetFilter: 'all',
	uptimeFilter: 'all',
	sortBy: 'user',
	sortDir: 'asc',
	showFilters: false,
	start() {
		this.$watch('search', () => { this.currentPage = 1; });
		this.$watch('perPage', () => { this.currentPage = 1; });
		this.$watch('subnetFilter', () => { this.currentPage = 1; });
		this.$watch('sortBy', () => { this.currentPage = 1; });
		this.$watch('sortDir', () => { this.currentPage = 1; });
		this.$watch('showFilters', (on) => {
			if (!on) this.clearAllFilters();
		});
		this.loadData();
	},
	async loadData() {
		this.loading = true;
		await this.refreshActiveUsers();
		this.loading = false;
	},
	async refreshActiveUsers() {
		try {
			const response = await window.axios.get('/api/active-users');
			if (Array.isArray(response.data)) {
				this.activeUsers = response.data;
			}
		} catch (error) {
			// Silent fail
		}
	},
	// --- Helper: parse uptime string to seconds ---
	_parseUptime(str) {
		if (!str || str === '-') return 0;
		let secs = 0;
		const w = str.match(/(\d+)w/); if (w) secs += parseInt(w[1]) * 604800;
		const d = str.match(/(\d+)d/); if (d) secs += parseInt(d[1]) * 86400;
		const h = str.match(/(\d+)h/); if (h) secs += parseInt(h[1]) * 3600;
		const m = str.match(/(\d+)m/); if (m) secs += parseInt(m[1]) * 60;
		const s = str.match(/(\d+)s/); if (s) secs += parseInt(s[1]);
		return secs;
	},
	// --- Unique IP subnets ---
	uniqueSubnets() {
		const subnets = new Set();
		this.activeUsers.forEach(au => {
			const ip = au.address || '';
			const parts = ip.split('.');
			if (parts.length === 4) {
				subnets.add(parts[0] + '.' + parts[1] + '.' + parts[2] + '.0/24');
			}
		});
		return [...subnets].sort();
	},
	// --- Subnet counts ---
	subnetCount(subnet) {
		const prefix = subnet.replace('.0/24', '.');
		return this.activeUsers.filter(au => (au.address || '').startsWith(prefix)).length;
	},
	// --- Uptime range counts ---
	uptimeRangeCount(range) {
		return this.activeUsers.filter(au => {
			const secs = this._parseUptime(au.uptime);
			switch (range) {
				case '<1h': return secs < 3600;
				case '1-6h': return secs >= 3600 && secs < 21600;
				case '6-24h': return secs >= 21600 && secs < 86400;
				case '>24h': return secs >= 86400;
				default: return true;
			}
		}).length;
	},
	// --- Active filter count ---
	activeFilterCount() {
		let count = 0;
		if (this.search.trim() !== '') count++;
		if (this.subnetFilter !== 'all') count++;
		return count;
	},
	toggleFilters() {
		this.showFilters = !this.showFilters;
	},
	clearAllFilters() {
		this.search = '';
		this.subnetFilter = 'all';
		this.sortBy = 'user';
		this.sortDir = 'asc';
		this.currentPage = 1;
	},
	// --- Stats ---
	statsTotalRx() {
		const total = this.activeUsers.reduce((sum, au) => sum + (au.rx_bytes || 0), 0);
		return this._formatBytes(total);
	},
	statsTotalTx() {
		const total = this.activeUsers.reduce((sum, au) => sum + (au.tx_bytes || 0), 0);
		return this._formatBytes(total);
	},
	_formatBytes(bytes) {
		if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(1) + ' GB';
		if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
		if (bytes >= 1024) return (bytes / 1024).toFixed(1) + ' KB';
		return bytes + ' B';
	},
	// --- Filtered + Sorted ---
	filteredActiveUsers() {
		let data = [...this.activeUsers];
		// Only apply filters when showFilters is on
		if (this.showFilters) {
			// Search filter
			const q = this.search.toLowerCase().trim();
			if (q) {
				data = data.filter(au => {
					const user = (au.user || '').toLowerCase();
					const addr = (au.address || '').toLowerCase();
					return user.includes(q) || addr.includes(q);
				});
			}
			// Subnet filter
			if (this.subnetFilter !== 'all') {
				const prefix = this.subnetFilter.replace('.0/24', '.');
				data = data.filter(au => (au.address || '').startsWith(prefix));
			}
			// Sorting
			const dir = this.sortDir === 'asc' ? 1 : -1;
			data.sort((a, b) => {
				let va, vb;
				switch (this.sortBy) {
					case 'user':
						va = (a.user || '').toLowerCase();
						vb = (b.user || '').toLowerCase();
						return va.localeCompare(vb) * dir;
					case 'address':
						va = (a.address || '').split('.').map(n => n.padStart(3, '0')).join('.');
						vb = (b.address || '').split('.').map(n => n.padStart(3, '0')).join('.');
						return va.localeCompare(vb) * dir;
					case 'uptime':
						va = this._parseUptime(a.uptime);
						vb = this._parseUptime(b.uptime);
						return (va - vb) * dir;
					case 'rx':
						return ((a.rx_bytes || 0) - (b.rx_bytes || 0)) * dir;
					case 'tx':
						return ((a.tx_bytes || 0) - (b.tx_bytes || 0)) * dir;
					default:
						return 0;
				}
			});
		}
		return data;
	},
	toggleSort(col) {
		if (this.sortBy === col) {
			this.sortDir = this.sortDir === 'asc' ? 'desc' : 'asc';
		} else {
			this.sortBy = col;
			this.sortDir = 'asc';
		}
	},
	sortIcon(col) {
		if (this.sortBy !== col) return 'fas fa-sort text-gray-300';
		return this.sortDir === 'asc' ? 'fas fa-sort-up text-green-500' : 'fas fa-sort-down text-green-500';
	},
	totalFiltered() {
		return this.filteredActiveUsers().length;
	},
	totalPages() {
		return Math.max(1, Math.ceil(this.totalFiltered() / this.perPage));
	},
	paginatedUsers() {
		const start = (this.currentPage - 1) * this.perPage;
		return this.filteredActiveUsers().slice(start, start + this.perPage);
	},
	pageStart() {
		if (this.totalFiltered() === 0) return 0;
		return (this.currentPage - 1) * this.perPage + 1;
	},
	pageEnd() {
		return Math.min(this.currentPage * this.perPage, this.totalFiltered());
	},
	goToPage(page) {
		if (page >= 1 && page <= this.totalPages()) {
			this.currentPage = page;
		}
	},
	nextPage() {
		this.goToPage(this.currentPage + 1);
	},
	prevPage() {
		this.goToPage(this.currentPage - 1);
	},
	visiblePages() {
		const total = this.totalPages();
		const current = this.currentPage;
		const pages = [];
		if (total <= 7) {
			for (let i = 1; i <= total; i++) pages.push(i);
		} else {
			pages.push(1);
			if (current > 3) pages.push('...');
			const start = Math.max(2, current - 1);
			const end = Math.min(total - 1, current + 1);
			for (let i = start; i <= end; i++) pages.push(i);
			if (current < total - 2) pages.push('...');
			pages.push(total);
		}
		return pages;
	},
}));

Alpine.start();
