(function() {
    // Variable global para el editor Quill
    let quillEditor = null;
    let initAttempts = 0;
    const maxAttempts = 10;

    // Función para insertar variables en el editor
    window.insertarVariableEnEditor = function(variable) {
        if (quillEditor) {
            const range = quillEditor.getSelection(true);
            quillEditor.insertText(range.index, variable, 'user');
            quillEditor.setSelection(range.index + variable.length);
        } else {
            console.log('⚠️ Editor Quill no está inicializado');
        }
    }

    // Función para inicializar Quill
    function inicializarQuillEditor() {
        const editorContainer = document.getElementById('quill-editor');
        
        if (!editorContainer) {
            console.error('❌ Contenedor #quill-editor no encontrado en el DOM');
            
            // Reintentar si no se ha alcanzado el máximo
            if (initAttempts < maxAttempts) {
                initAttempts++;
                console.log(`🔄 Reintentando (${initAttempts}/${maxAttempts})...`);
                setTimeout(() => inicializarQuillEditor(), 200);
            }
            return;
        }
        
        // Verificar que el contenedor sea visible
        const rect = editorContainer.getBoundingClientRect();
        if (rect.height === 0 || rect.width === 0) {
            console.warn('⚠️ Contenedor no visible aún, esperando...');
            if (initAttempts < maxAttempts) {
                initAttempts++;
                setTimeout(() => inicializarQuillEditor(), 200);
            }
            return;
        }
        
        // Si ya existe un editor, destruirlo primero
        if (quillEditor) {
            console.log('🔄 Destruyendo editor Quill existente...');
            editorContainer.innerHTML = '';
            quillEditor = null;
        }
        
        console.log('🚀 Inicializando Quill Editor...');
        
        // Configuración de la barra de herramientas
        const toolbarOptions = [
            [{ 'header': [1, 2, 3, false] }],
            ['bold', 'italic', 'underline', 'strike'],
            [{ 'color': [] }, { 'background': [] }],
            [{ 'align': [] }],
            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
            [{ 'indent': '-1'}, { 'indent': '+1' }],
            ['link', 'image'],
            ['blockquote', 'code-block'],
            ['clean']
        ];

        try {
            // Inicializar Quill
            quillEditor = new Quill('#quill-editor', {
                theme: 'snow',
                modules: {
                    toolbar: toolbarOptions
                },
                placeholder: 'Escribe aquí el contenido de tu correo...',
            });

            // Cargar contenido inicial si existe
            const lw = window.Livewire.find(window.PlantillasCorreosTabConfig?.componentId);
            const contenidoInicial = lw?.plantillaContenidoHtml || '';
            if (contenidoInicial && contenidoInicial.trim() !== '') {
                console.log('📄 Cargando contenido inicial...');
                quillEditor.root.innerHTML = contenidoInicial;
            }

            // Sincronizar cambios con Livewire
            quillEditor.on('text-change', function() {
                const html = quillEditor.root.innerHTML;
                lw?.set('plantillaContenidoHtml', html);
            });
            
            console.log('✅ Quill Editor inicializado correctamente');
            initAttempts = 0; // Resetear contador
        } catch (error) {
            console.error('❌ Error al inicializar Quill:', error);
        }
    }

    // Escuchar evento de Livewire cuando se abre el modal del editor
    Livewire.on('plantilla-editor-opened', () => {
        console.log('📩 Evento plantilla-editor-opened recibido');
        initAttempts = 0; // Resetear contador de intentos
        
        // Esperar a que el modal esté completamente renderizado
        setTimeout(() => {
            inicializarQuillEditor();
        }, 500);
    });
})();

if (typeof abrirVistaPreviaPlantilla === 'function') window.abrirVistaPreviaPlantilla = abrirVistaPreviaPlantilla;
if (typeof insertarVariableEnEditor === 'function') window.insertarVariableEnEditor = insertarVariableEnEditor;
