    class PermissionsTableManager {
        constructor() {
            this.init();
        }

        init() {
            this.setupEventListeners();
            this.initializeTable();
        }

        setupEventListeners() {
            document.addEventListener('livewire:load', () => this.initializeTable());
            document.addEventListener('livewire:update', () => this.initializeTable());
            document.addEventListener('livewire:navigated', () => this.initializeTable());
            
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => this.initializeTable());
            } else {
                this.initializeTable();
            }
        }

        initializeTable() {
            this.setupDeleteButtons();
        }

        setupDeleteButtons() {
            const buttons = document.querySelectorAll('.delete-permission-btn');
            buttons.forEach(btn => {
                if (btn.deleteHandler) {
                    btn.removeEventListener('click', btn.deleteHandler);
                }
                
                const permissionId = btn.dataset.permissionId;
                const permissionName = btn.dataset.permissionName;
                
                const deleteHandler = (e) => {
                    e.preventDefault();
                    this.handleDelete(permissionId, permissionName);
                };

                btn.deleteHandler = deleteHandler;
                btn.addEventListener('click', deleteHandler);
            });
        }

        async handleDelete(permissionId, permissionName) {
            if (typeof Swal !== 'undefined') {
                const result = await Swal.fire({
                    title: '¿Estás seguro?',
                    text: `¿Estás seguro de que quieres eliminar el permiso "${permissionName}"? Esta acción no se puede deshacer.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#D12420',
                    cancelButtonColor: '#8D8D8D',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar',
                });

                if (result.isConfirmed) {
                    this.performDelete(permissionId);
                }
            } else {
                if (confirm(`¿Estás seguro de que quieres eliminar el permiso "${permissionName}"?`)) {
                    this.performDelete(permissionId);
                }
            }
        }

        async performDelete(permissionId) {
            try {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/permission/permission_delete/${permissionId}`;
                form.style.display = 'none';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (csrfToken) {
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);
                }

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            } catch (error) {
                console.error('Error al eliminar permiso:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', 'Error al eliminar el permiso. Intenta recargar la página.', 'error');
                } else {
                    alert('Error al eliminar el permiso. Intenta recargar la página.');
                }
            }
        }
    }

    const permissionsTable = new PermissionsTableManager();
