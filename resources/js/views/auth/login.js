        document.addEventListener('DOMContentLoaded', function() {
            const intendedUrl = localStorage.getItem('intended_url');
            
            if (intendedUrl) {
                localStorage.removeItem('intended_url');
                
                const form = document.querySelector('form[method="POST"]');
                if (form) {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'intended_url';
                    hiddenInput.value = intendedUrl;
                    form.appendChild(hiddenInput);
                }
            }
        });
