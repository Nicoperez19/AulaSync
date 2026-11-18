    function modalComponent({
        show = false,
        focusable = false
    }) {
        return {
            show,
            init() {
                this.$watch('show', value => {
                    document.body.classList.toggle('overflow-y-hidden', value);
                    if (value && focusable) {
                        setTimeout(() => this.firstFocusable()?.focus(), 100);
                    }
                });
            },
            handleOpen(event, name) {
                if (event.detail === name) this.show = true;
            },
            handleClose(event, name) {
                if (event.detail === name) this.show = false;
            },
            focusables() {
                return [...this.$el.querySelectorAll(
                    'a, button, input:not([type="hidden"]), textarea, select, details, [tabindex]:not([tabindex="-1"])'
                )].filter(el => !el.disabled);
            },
            firstFocusable() {
                return this.focusables()[0];
            },
            lastFocusable() {
                return this.focusables().at(-1);
            },
            navigateFocus(event) {
                const focusables = this.focusables();
                const index = focusables.indexOf(document.activeElement);
                const direction = event.shiftKey ? -1 : 1;
                const next = (index + direction + focusables.length) % focusables.length;
                focusables[next]?.focus();
            },
        };
    }

window.modalComponent = modalComponent;
