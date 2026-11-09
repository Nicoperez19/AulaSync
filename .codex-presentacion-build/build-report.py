from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_TAB_ALIGNMENT, WD_TAB_LEADER
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_CELL_VERTICAL_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn
from pathlib import Path

OUT = Path(r'C:\Users\carte\Documents\GitHub\AulaSync\docs\Informe_Avance_Proyecto_AulaSync.docx')
LOGO = Path(r'C:\Users\carte\Documents\GitHub\AulaSync\.codex-presentacion-build\ucsc-logo-reference.png')
OUT.parent.mkdir(parents=True, exist_ok=True)

doc = Document()
section = doc.sections[0]
section.page_width = Inches(8.5)
section.page_height = Inches(11)
section.top_margin = Inches(0.82)
section.bottom_margin = Inches(0.78)
section.left_margin = Inches(0.95)
section.right_margin = Inches(0.95)
section.different_first_page_header_footer = True
section.header_distance = Inches(0.22)

styles = doc.styles
normal = styles['Normal']
normal.font.name = 'Arial'
normal.font.size = Pt(11)
normal.font.color.rgb = RGBColor(35, 35, 35)
normal.paragraph_format.space_after = Pt(7)
normal.paragraph_format.line_spacing = 1.14
for stylename, size in [('Title', 26), ('Heading 1', 16), ('Heading 2', 12.5), ('Heading 3', 11.5)]:
    st = styles[stylename]
    st.font.name = 'Arial'
    st.font.size = Pt(size)
    st.font.bold = True
    st.font.color.rgb = RGBColor(0, 0, 0)
    st.paragraph_format.keep_with_next = True
    st.paragraph_format.space_before = Pt(13 if stylename != 'Title' else 0)
    st.paragraph_format.space_after = Pt(6)

# Remove the built-in Word Title style's blue paragraph border.
title_ppr = styles['Title']._element.get_or_add_pPr()
for child in list(title_ppr):
    if child.tag == qn('w:pBdr'):
        title_ppr.remove(child)

def set_cell_shading(cell, fill):
    tc_pr = cell._tc.get_or_add_tcPr()
    shd = OxmlElement('w:shd')
    shd.set(qn('w:fill'), fill)
    tc_pr.append(shd)

def set_cell_margins(cell, top=100, start=110, bottom=100, end=110):
    tc = cell._tc
    tc_pr = tc.get_or_add_tcPr()
    margins = tc_pr.first_child_found_in('w:tcMar')
    if margins is None:
        margins = OxmlElement('w:tcMar')
        tc_pr.append(margins)
    for m, v in [('top', top), ('start', start), ('bottom', bottom), ('end', end)]:
        node = margins.find(qn(f'w:{m}'))
        if node is None:
            node = OxmlElement(f'w:{m}')
            margins.append(node)
        node.set(qn('w:w'), str(v))
        node.set(qn('w:type'), 'dxa')

def set_table_borders(table):
    tbl_pr = table._tbl.tblPr
    borders = OxmlElement('w:tblBorders')
    for edge in ('top', 'left', 'bottom', 'right', 'insideH', 'insideV'):
        el = OxmlElement(f'w:{edge}')
        el.set(qn('w:val'), 'single')
        el.set(qn('w:sz'), '5')
        el.set(qn('w:space'), '0')
        el.set(qn('w:color'), 'D9D9D9')
        borders.append(el)
    tbl_pr.append(borders)

def add_table(headers, rows, widths=None):
    table = doc.add_table(rows=1, cols=len(headers))
    table.alignment = WD_TABLE_ALIGNMENT.CENTER
    table.autofit = False
    set_table_borders(table)
    for i, header in enumerate(headers):
        cell = table.rows[0].cells[i]
        cell.text = header
        set_cell_shading(cell, '8D0018')
        cell.vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER
        set_cell_margins(cell, 125, 125, 125, 125)
        for p in cell.paragraphs:
            p.paragraph_format.space_after = Pt(0)
            for r in p.runs:
                r.bold = True
                r.font.name = 'Arial'
                r.font.size = Pt(10)
                r.font.color.rgb = RGBColor(255, 255, 255)
    for row_index, values in enumerate(rows):
        cells = table.add_row().cells
        for i, value in enumerate(values):
            cells[i].text = value
            cells[i].vertical_alignment = WD_CELL_VERTICAL_ALIGNMENT.CENTER
            set_cell_margins(cells[i])
            if row_index % 2 == 1:
                set_cell_shading(cells[i], 'F4F6F8')
            for p in cells[i].paragraphs:
                p.paragraph_format.space_after = Pt(1)
                p.paragraph_format.line_spacing = 1.05
                for r in p.runs:
                    r.font.name = 'Arial'
                    r.font.size = Pt(9.5)
                    r.font.color.rgb = RGBColor(35, 35, 35)
    if widths:
        for row in table.rows:
            for i, width in enumerate(widths):
                row.cells[i].width = Inches(width)
    doc.add_paragraph().paragraph_format.space_after = Pt(1)
    return table

def p(text='', bold_lead=None, style=None, align=None):
    para = doc.add_paragraph(style=style)
    if align is not None:
        para.alignment = align
    if bold_lead and text.startswith(bold_lead):
        para.add_run(bold_lead).bold = True
        para.add_run(text[len(bold_lead):])
    else:
        para.add_run(text)
    return para

def bullet(text):
    para = doc.add_paragraph(style='List Bullet')
    para.paragraph_format.left_indent = Inches(0.28)
    para.paragraph_format.first_line_indent = Inches(-0.14)
    para.paragraph_format.space_after = Pt(4)
    para.add_run(text)
    return para

def heading(text, level=1):
    return doc.add_heading(text, level=level)

def field(run, instruction):
    begin = OxmlElement('w:fldChar'); begin.set(qn('w:fldCharType'), 'begin')
    instr = OxmlElement('w:instrText'); instr.set(qn('xml:space'), 'preserve'); instr.text = instruction
    separate = OxmlElement('w:fldChar'); separate.set(qn('w:fldCharType'), 'separate')
    text = OxmlElement('w:t'); text.text = '1'
    end = OxmlElement('w:fldChar'); end.set(qn('w:fldCharType'), 'end')
    run._r.extend([begin, instr, separate, text, end])

# Running header and footer, matching the UCSC identity shown in the reference report.
section.different_first_page_header_footer = False
header = section.header
hp = header.paragraphs[0]
hp.alignment = WD_ALIGN_PARAGRAPH.LEFT
hp.paragraph_format.space_after = Pt(0)
hp.paragraph_format.line_spacing = 1.0
hp.paragraph_format.tab_stops.add_tab_stop(Inches(1.72), WD_TAB_ALIGNMENT.LEFT)
logo_run = hp.add_run()
logo_run.add_picture(str(LOGO), width=Inches(1.55))
brand = hp.add_run('\tUNIVERSIDAD CATÓLICA DE LA SANTÍSIMA CONCEPCIÓN\n\tFACULTAD DE INGENIERÍA\n\tINGENIERÍA CIVIL INFORMÁTICA')
brand.font.name = 'Arial'; brand.font.size = Pt(8); brand.font.color.rgb = RGBColor(35, 35, 35)
footer = section.footer
fp = footer.paragraphs[0]
fp.alignment = WD_ALIGN_PARAGRAPH.CENTER
fr = fp.add_run('Informe de avance AulaSync  ·  Página ')
fr.font.name = 'Arial'; fr.font.size = Pt(8); fr.font.color.rgb = RGBColor(100, 100, 100)
field(fp.add_run(), ' PAGE ')

# Portada
for _ in range(2):
    doc.add_paragraph()
for _ in range(5):
    doc.add_paragraph()
title = doc.add_paragraph(style='Title')
title.alignment = WD_ALIGN_PARAGRAPH.CENTER
title_ppr = title._p.get_or_add_pPr()
for child in list(title_ppr):
    if child.tag == qn('w:pBdr'):
        title_ppr.remove(child)
title.add_run('INFORME DE AVANCE DEL PROYECTO')
subtitle = doc.add_paragraph()
subtitle.alignment = WD_ALIGN_PARAGRAPH.CENTER
subtitle.paragraph_format.space_before = Pt(10)
subtitle.paragraph_format.space_after = Pt(12)
sr = subtitle.add_run('Mejoras al módulo de soporte de AulaSync')
sr.bold = True; sr.font.name = 'Arial'; sr.font.size = Pt(17); sr.font.color.rgb = RGBColor(0, 0, 0)
project = doc.add_paragraph()
project.alignment = WD_ALIGN_PARAGRAPH.CENTER
project.add_run('Instituto Tecnológico UCSC · Sede Talcahuano').font.size = Pt(12)
for _ in range(4):
    doc.add_paragraph()
author = doc.add_paragraph()
author.alignment = WD_ALIGN_PARAGRAPH.CENTER
ar = author.add_run('Javiera Belén Matus Morales'); ar.bold = True; ar.font.size = Pt(12)
place = doc.add_paragraph()
place.alignment = WD_ALIGN_PARAGRAPH.CENTER
place.add_run('Concepción, octubre de 2026').font.size = Pt(11)
doc.add_page_break()

# Resumen e índice
heading('Resumen ejecutivo', 1)
p('Este informe cuenta los cambios hechos en la parte de ayuda de AulaSync. Ahora las personas pueden revisar sus solicitudes y ver en qué estado están. El equipo de soporte tiene una pantalla para buscar y atenderlas. También se mejoraron los gráficos y los filtros, y se ordenaron unos botones del panel principal para que no se vean amontonados.')
p('Se agregó el tipo de usuario Soporte y se revisó quién puede ver o atender cada solicitud. Se creó una ventana con el estado, el detalle y las respuestas. Los gráficos muestran las solicitudes abiertas en azul, las que están en proceso en amarillo y las cerradas en rojo. Las opciones de búsqueda actualizan la lista al seleccionarlas. También se ordenaron los botones “Esta Semana”, “Este Mes” y “Hoy”. Se revisó el código y las pantallas, pero no se hicieron pruebas automáticas.')
p('Mi aporte fue programar y ajustar estas funciones para que sea más fácil encontrar una solicitud y entender lo que ha ocurrido con ella.')
heading('Contenido', 1)
for item, page in [
    ('Resumen ejecutivo', '2'),
    ('Capítulo 1 Introducción y objetivos', '3'),
    ('Capítulo 2 Contexto institucional y del proyecto', '3'),
    ('Capítulo 3 Actividades y aporte personal', '4'),
    ('Capítulo 4 Resultados y revisión realizada', '5'),
    ('Capítulo 5 Reflexión', '6'),
    ('Capítulo 6 Conclusiones', '6'),
    ('Referencias', '6'),
]:
    para = doc.add_paragraph()
    para.paragraph_format.space_after = Pt(5)
    para.paragraph_format.left_indent = Inches(0.08)
    para.paragraph_format.tab_stops.add_tab_stop(Inches(6.45), WD_TAB_ALIGNMENT.RIGHT, WD_TAB_LEADER.DOTS)
    para.add_run(f'{item}\t{page}')
doc.add_page_break()

# Capítulo 1
heading('Capítulo 1 Introducción y objetivos', 1)
heading('1.1 Introducción', 2)
p('AulaSync es un sitio web que ayuda a instituciones educativas a organizar salas, horarios, reservas y asistencia. En este proyecto mejoré la parte que permite pedir ayuda y revisar las respuestas del equipo de soporte.')
p('En las siguientes páginas explico dónde se usa el proyecto, qué cambios hice y qué pude revisar. El informe incluye solo el trabajo realizado hasta ahora.')
heading('1.2 Situación que se buscó mejorar', 2)
p('La lista de solicitudes no mostraba sus estados de una forma fácil de comparar. Las personas también necesitaban revisar el estado y las respuestas de cada solicitud. El equipo necesitaba una pantalla para encontrar y atender los casos que le corresponden.')
p('En el panel de clases, los botones de semana, mes y día estaban muy juntos. Además, se quería que los filtros de soporte cambiaran los resultados al elegir una opción, sin tener que apretar otro botón.')
heading('1.3 Objetivo general', 2)
p('Mejorar la parte de ayuda de AulaSync para que las personas puedan revisar sus solicitudes y el equipo pueda ordenarlas y responderlas con claridad.')
heading('1.4 Objetivos específicos', 2)
for item in [
    'Agregar un tipo de usuario y un permiso para las tareas de soporte.',
    'Facilitar la búsqueda de solicitudes y la revisión de sus respuestas.',
    'Mostrar cuántas solicitudes están abiertas, en proceso o cerradas.',
    'Crear una pantalla para que el equipo de soporte revise los casos por atender.',
    'Separar los botones de semana, mes y día del panel de clases.',
]: bullet(item)

# Capítulo 2
heading('Capítulo 2 Contexto institucional y del proyecto', 1)
heading('2.1 Contexto institucional', 2)
p('Este proyecto se desarrolla en el Instituto Tecnológico de la Universidad Católica de la Santísima Concepción, sede Talcahuano. Allí, AulaSync ayuda a organizar salas, reservas, horarios y asistencia.')
heading('2.2 Descripción de AulaSync', 2)
p('AulaSync es un sitio web para instituciones educativas. Permite organizar espacios, reservas, horarios y asistencia. La información se ordena por sede para que cada una vea sus propios datos.')
p('La parte de soporte permite pedir ayuda y revisar las respuestas. El equipo encargado puede ver las solicitudes y cambiar su estado. En este informe uso principalmente la palabra “solicitud”.')
heading('2.3 Herramientas utilizadas', 2)
p('AulaSync se construyó con varias herramientas. Laravel y PHP procesan la información y aplican las reglas. Blade y Tailwind CSS ayudan a mostrar las pantallas. Alpine.js permite que los filtros cambien los resultados y que se abra la ventana de detalle. Los datos se guardan en MariaDB o MySQL.')
add_table(['Parte del sistema', 'Herramientas', 'Uso en este trabajo'], [
    ('Procesamiento', 'Laravel y PHP', 'Buscar solicitudes y revisar quién puede atenderlas.'),
    ('Pantallas', 'Blade y Tailwind CSS', 'Mostrar listas, gráficos y respuestas.'),
    ('Acciones en pantalla', 'Alpine.js', 'Actualizar filtros y abrir la ventana de detalle.'),
    ('Almacenamiento', 'MariaDB o MySQL', 'Guardar solicitudes, estados, sedes y respuestas.'),
], [1.25, 1.65, 3.9])

# Capítulo 3
heading('Capítulo 3 Actividades y aporte personal', 1)
p('Trabajé principalmente en las pantallas de soporte y en el orden de los botones del panel de clases. A continuación, explico los cambios con palabras simples.')
heading('3.1 Acceso y permisos de soporte', 2)
p('Agregué el tipo de usuario “Soporte” y el permiso para atender solicitudes. También puse el acceso al panel de soporte en el menú del sistema, para que el equipo pueda encontrarlo.')
p('Ajusté las reglas para revisar quién puede ver o atender cada solicitud. El sistema toma en cuenta el tipo de usuario, sus permisos y la sede. Las personas pueden revisar sus propios casos, pero no administrar los de otras personas.')
heading('3.2 Vista de soporte para el equipo', 2)
p('Creé una pantalla para que el equipo vea las solicitudes abiertas y las que están en proceso. La lista muestra el título, quién pidió ayuda, la sede, la prioridad, el estado y quién está atendiendo el caso. Desde allí se puede abrir cada solicitud.')
p('El equipo puede elegir una sede y la lista cambia sola. También dejé una vista de prueba: si la persona no tiene permiso de soporte, solo puede ver sus propios casos.')
section_heading = heading('3.3 Lista y seguimiento de solicitudes', 2)
section_heading.paragraph_format.keep_with_next = True
paragraph = p('En la lista agregué un gráfico que muestra cuántas solicitudes están abiertas, en proceso o cerradas. Los colores son azul, amarillo y rojo, respectivamente.')
paragraph.paragraph_format.keep_with_next = True
paragraph = p('Agregué opciones para elegir sede, estado y prioridad. La lista cambia al seleccionar una opción. La búsqueda se actualiza poco después de dejar de escribir.')
paragraph.paragraph_format.keep_with_next = True
p('Al elegir una solicitud, se abre una ventana con su estado, sus pasos de avance, la descripción y las respuestas del equipo. También corregí la posición de esta ventana para que la barra superior no la tape.')
heading('3.4 Gráfico del panel de soporte', 2)
p('Agregué un gráfico al panel de soporte para resumir las solicitudes. Al elegir una sede, cambian los números del gráfico y la lista muestra los casos por atender. También se puede abrir la lista completa.')
p('El gráfico usa los mismos colores que la lista: azul para solicitudes abiertas, amarillo para las que están en proceso y rojo para las cerradas.')
heading('3.5 Ajuste de períodos del panel de clases', 2)
p('Ordené los botones “Esta Semana”, “Este Mes” y “Hoy” en una fila con espacio entre ellos. Así dejan de chocar y se ven mejor. También siguen disponibles los campos de fecha y el botón para buscar un período elegido por el usuario.')

# Capítulo 4
heading('Capítulo 4 Resultados y revisión realizada', 1)
heading('4.1 Resultados alcanzados', 2)
add_table(['Área', 'Cambio realizado', 'Resultado visible'], [
    ('Acceso', 'Rol Soporte, permiso específico y comprobaciones de acceso.', 'Las funciones de soporte quedan separadas según perfil y alcance.'),
    ('Consulta', 'Listado con gráfico, estados y filtros automáticos.', 'Es más sencillo encontrar solicitudes y entender su distribución.'),
    ('Seguimiento', 'Ventana con estado, avances, descripción y respuestas.', 'La persona puede revisar su solicitud en una sola pantalla.'),
    ('Panel de soporte', 'Gráfico por estado, opción de sede y lista de solicitudes.', 'El equipo puede ver un resumen y encontrar los casos por atender.'),
    ('Panel de clases', 'Botones de semana, mes y día en una fila ordenada.', 'Los botones quedan separados y no se superponen.'),
], [1.15, 2.85, 2.8])
heading('4.2 Comprobaciones realizadas', 2)
p('Revisé que el archivo que maneja las solicitudes no tuviera errores de escritura y que las pantallas se pudieran preparar para mostrarse. Esto no reemplaza probar todas las funciones. No se hicieron pruebas automáticas ni se midió el uso o la velocidad del sistema.')

# Capítulo 5
heading('Capítulo 5 Reflexión', 1)
p('Este trabajo me permitió usar lo aprendido para resolver una necesidad real del sistema. También me mostró que hay que revisar quién puede ver cada solicitud y qué puede hacer cada persona.')
p('Los cambios se pensaron para que el sistema sea más fácil de usar. El gráfico resume los estados, los filtros ayudan a encontrar casos y la ventana reúne la información importante. Separar los botones del panel de clases también hizo más clara esa pantalla.')
p('Revisé que el código y las pantallas no tuvieran errores básicos, pero no hice pruebas automáticas. Por eso, el informe aclara qué se revisó y qué no.')

# Capítulo 6
heading('Capítulo 6 Conclusiones', 1)
p('Mejoré la parte de ayuda de AulaSync para que las personas puedan revisar sus solicitudes y el equipo pueda atenderlas con más orden. Agregué el acceso de soporte, una pantalla para el equipo, filtros automáticos, gráficos y una ventana para leer el estado y las respuestas.')
p('También ordené los botones de semana, mes y día en el panel de clases. Estos cambios ayudan a encontrar información y entenderla mejor. Revisé el código y las pantallas, pero no hice pruebas automáticas.')

# Referencias
heading('Referencias', 1)
p('AulaSync. (2026). README del proyecto. Repositorio de trabajo.', bold_lead='AulaSync.')
p('AulaSync. (2026). Controlador de soporte SupportTicketController.php. Repositorio de trabajo.', bold_lead='AulaSync.')
p('AulaSync. (2026). Vistas de soporte: index.blade.php y dashboard.blade.php. Repositorio de trabajo.', bold_lead='AulaSync.')
p('AulaSync. (2026). Parcial de períodos del dashboard de clases status_clases_chart.blade.php. Repositorio de trabajo.', bold_lead='AulaSync.')
p('AulaSync. (2026). Rutas del módulo de soporte, menú lateral y RoleSeeder.php. Repositorio de trabajo.', bold_lead='AulaSync.')

# Keep headings together with following text and avoid widowed headings.
for para in doc.paragraphs:
    if para.style.name.startswith('Heading'):
        para.paragraph_format.keep_with_next = True

doc.save(OUT)
print(OUT)
