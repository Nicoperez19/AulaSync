import fs from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const skillDir = 'C:/Users/carte/.codex/plugins/cache/openai-primary-runtime/presentations/26.930.11008/skills/Presentations';
const templatePath = 'C:/Users/carte/Downloads/Plantilla_IT_Talcahuano.pptx';
const workspaceDir = 'C:/Users/carte/Documents/GitHub/AulaSync';
const buildDir = path.join(workspaceDir, '.codex-presentacion-build');
const finalPath = path.join(workspaceDir, 'docs', 'Informe_AulaSync_Presentacion.pptx');
const candidatePath = path.join(buildDir, 'candidate.pptx');
const { importRuntimeModule } = await import(pathToFileURL(path.join(skillDir, 'container_tools/runtime_helpers.mjs')).href);
const { FileBlob, PresentationFile } = await importRuntimeModule('@oai/artifact-tool');
const { finalizePresentation } = await import(pathToFileURL(path.join(skillDir, 'container_tools/artifact_tool_utils.mjs')).href);
await fs.mkdir(buildDir, { recursive: true });
await fs.mkdir(path.dirname(finalPath), { recursive: true });
if (await fs.stat(finalPath).then(() => true).catch(() => false)) throw new Error(`Output already exists: ${finalPath}`);

const presentation = await PresentationFile.importPptx(await FileBlob.load(templatePath));
const snapshot = await presentation.inspect({ kind: 'slide,textbox,shape', maxChars: 30000 });
const records = snapshot.ndjson.split('\n').filter(Boolean).map(line => JSON.parse(line));
const anchor = (kind, slideIndex, name) => {
  const record = records.find(item => item.kind === kind && item.slideIndex === slideIndex && item.name === name);
  if (!record) throw new Error(`Could not locate ${name} on slide ${slideIndex + 1}`);
  return presentation.resolve(record.id);
};

// Cover: retain the supplied photo, UCSC identity, Montserrat typography and layout.
anchor('textbox', 0, 'CuadroTexto 3').text = 'AulaSync: módulo de soporte';
const coverSubtitle = anchor('textbox', 0, 'CuadroTexto 4');
coverSubtitle.text = 'Informe de avance\nInstituto Tecnológico · Sede Talcahuano';
coverSubtitle.text.style = {
  typeface: 'Montserrat', fontSize: 25, bold: true, color: '#404040', alignment: 'left',
  verticalAlignment: 'top', autoFit: 'shrinkText', wrap: 'square',
};

const BRAND = '#D00120';
const BODY = '#404040';
const MUTED = '#646363';
const FONT = 'Montserrat';
function addDot(slide, name, left, top, color) {
  return slide.shapes.add({ geometry: 'ellipse', name, position: { left, top, width: 14, height: 14 }, fill: color, line: { style: 'solid', fill: color, width: 0 } });
}
function addText(slide, name, text, position, style = {}) {
  const shape = slide.shapes.add({
    geometry: 'textbox', name, position, fill: 'none',
    line: { style: 'solid', fill: 'none', width: 0 },
  });
  shape.text = text;
  shape.text.style = {
    typeface: style.typeface ?? FONT,
    fontSize: style.fontSize ?? 22,
    bold: style.bold ?? false,
    color: style.color ?? BODY,
    alignment: style.alignment ?? 'left',
    verticalAlignment: 'top',
    autoFit: 'shrinkText',
    wrap: 'square',
    insets: { top: 0, right: 0, bottom: 0, left: 0 },
    ...(style.lineSpacing ? { lineSpacing: style.lineSpacing } : {}),
  };
  return shape;
}
function addRule(slide, name, left, top, width, height = 3, color = BRAND) {
  return slide.shapes.add({
    geometry: 'rect', name, position: { left, top, width, height }, fill: color,
    line: { style: 'solid', fill: color, width: 0 },
  });
}
function section(slide, name, left, top, width, heading, body, bodySize = 22) {
  addText(slide, `${name}-heading`, heading, { left, top, width, height: 34 }, { fontSize: 24, bold: true, color: BRAND });
  addRule(slide, `${name}-accent`, left, top + 40, 42, 4);
  addText(slide, `${name}-body`, body, { left, top: top + 58, width, height: 270 }, { fontSize: bodySize, color: MUTED, lineSpacing: 1.15 });
}
function setTitle(slide, text) {
  addText(slide, 'template-title', text, { left: 43.13, top: 38.33, width: 1196.52, height: 139.17 },
    { typeface: FONT, fontSize: 58.67, color: BRAND });
}
function addNotes(slide, text) {
  slide.speakerNotes.textFrame.setText(text);
}

// Slide 2: context and need.
const contextSlide = presentation.slides.getItem(1);
const contextTitle = anchor('shape', 1, 'Título 1');
contextTitle.text = 'Contexto y necesidad';
contextTitle.text.style = { typeface: 'Montserrat', fontSize: 58.67, color: BRAND, alignment: 'left' };
anchor('shape', 1, 'Marcador de contenido 2').delete();
section(contextSlide, 'context', 62, 220, 500, 'Contexto institucional',
  'AulaSync es una plataforma web pensada para instituciones educativas.\n\nAyuda a organizar salas, reservas, horarios académicos y asistencia.\n\nEste proyecto se desarrolla en el entorno del Instituto Tecnológico UCSC, sede Talcahuano.', 21);
addRule(contextSlide, 'context-divider', 630, 220, 2, 320, '#D9D9D9');
section(contextSlide, 'need', 690, 220, 520, 'Necesidad atendida',
  'Quien solicita apoyo necesita saber qué ocurre con su ticket.\n\nEl equipo de soporte requiere revisar las solicitudes, responder y ver con claridad cuáles corresponden a cada sede.', 22);
addNotes(contextSlide, 'Fuentes: README.md, descripción de AulaSync; docs/INFORME_CALCULOS_DASHBOARD.md, identificación del Instituto Tecnológico UCSC y sede Talcahuano.');

// Slide 3: plain-language solution.
const solutionSlide = presentation.slides.getItem(2);
const solutionTitle = anchor('shape', 2, 'Título 1');
solutionTitle.text = 'Solución planteada';
solutionTitle.text.style = { typeface: 'Montserrat', fontSize: 58.67, color: BRAND, alignment: 'left' };
anchor('shape', 2, 'Marcador de contenido 2').delete();
addText(solutionSlide, 'solution-intro',
  'Se mejoró el módulo de soporte para que pedir ayuda, seguirla y atenderla resulte más claro.',
  { left: 62, top: 215, width: 1140, height: 62 }, { fontSize: 26, bold: true, color: BODY });
section(solutionSlide, 'solution-user', 62, 316, 330, 'Consultar',
  'Cada persona puede revisar sus propios tickets, conocer su estado y leer las respuestas.', 21);
addRule(solutionSlide, 'solution-divider-1', 425, 315, 2, 245, '#D9D9D9');
section(solutionSlide, 'solution-staff', 470, 316, 330, 'Atender',
  'El equipo puede buscar solicitudes, ver su distribución y dar seguimiento desde el sistema.', 21);
addRule(solutionSlide, 'solution-divider-2', 835, 315, 2, 245, '#D9D9D9');
section(solutionSlide, 'solution-scope', 880, 316, 330, 'Cuidar el acceso',
  'Las acciones de soporte respetan la sede correspondiente; cada persona mantiene acceso a sus propios tickets.', 21);
addNotes(solutionSlide, 'Fuentes: app/Http/Controllers/SupportTicketController.php; resources/views/soporte/index.blade.php; resources/views/soporte/dashboard.blade.php.');

// Add three branded content slides before the template's closing slide.
let cursor = solutionSlide;
function addContentSlide(title) {
  const inserted = presentation.slides.insert({ after: cursor, layoutId: '/ppt/slideLayouts/slideLayout2.xml' });
  const slide = inserted.slide;
  cursor = slide;
  setTitle(slide, title);
  return slide;
}

// Slide 4: user's view and permissions.
const accessSlide = addContentSlide('Aporte personal: acceso y seguimiento');
section(accessSlide, 'permissions', 62, 230, 510, 'Permisos y sedes',
  'Se incorporó el rol Soporte y su permiso para atender tickets. Ajusté las comprobaciones para que el personal gestione solicitudes dentro de su alcance.\n\nLos usuarios pueden seguir administrando sus propios tickets.', 21);
addRule(accessSlide, 'access-divider', 630, 230, 2, 300, '#D9D9D9');
section(accessSlide, 'tracking', 690, 230, 520, 'Seguimiento del ticket',
  'Añadí una ventana de detalle que muestra el estado, los pasos de avance, la descripción y las respuestas. También corregí su posición para que la barra superior no la cubra.', 21);
addNotes(accessSlide, 'Fuentes: database/seeders/RoleSeeder.php; app/Http/Controllers/SupportTicketController.php; resources/views/soporte/index.blade.php. El rol y permiso aparecen en el trabajo registrado en docs/BITACORA_SEMANAL_2026-09-21_2026-09-27.md.');

// Slide 5: charts and automatic filters.
const chartsSlide = addContentSlide('Aporte personal: gráficos y filtros');
section(chartsSlide, 'charts', 62, 225, 510, 'Gráficos por estado',
  'Incorporé gráficos circulares en la vista de solicitudes y en el panel de soporte. Muestran cantidades y porcentajes de tickets abiertos, en proceso y cerrados.', 21);
addDot(chartsSlide, 'legend-open-dot', 62, 530, '#3b82f6');
addText(chartsSlide, 'legend-open-label', 'Abiertos', { left: 82, top: 526, width: 92, height: 24 }, { fontSize: 17, bold: true, color: MUTED });
addDot(chartsSlide, 'legend-progress-dot', 202, 530, '#eab308');
addText(chartsSlide, 'legend-progress-label', 'En proceso', { left: 222, top: 526, width: 115, height: 24 }, { fontSize: 17, bold: true, color: MUTED });
addDot(chartsSlide, 'legend-closed-dot', 365, 530, '#ef4444');
addText(chartsSlide, 'legend-closed-label', 'Cerrados', { left: 385, top: 526, width: 90, height: 24 }, { fontSize: 17, bold: true, color: MUTED });
addRule(chartsSlide, 'charts-divider', 630, 225, 2, 330, '#D9D9D9');
section(chartsSlide, 'filters', 690, 225, 520, 'Filtros de uso directo',
  'Los filtros de sede, estado y prioridad se aplican al seleccionarlos. La búsqueda se actualiza al terminar de escribir. En el panel de soporte, el gráfico y la lista se ajustan al filtro de sede.', 21);
addNotes(chartsSlide, 'Fuentes: resources/views/soporte/index.blade.php; resources/views/soporte/dashboard.blade.php; app/Http/Controllers/SupportTicketController.php.');

// Slide 6: dashboard layout adjustment and verification.
const validationSlide = addContentSlide('Ajustes y comprobación');
section(validationSlide, 'period', 62, 230, 510, 'Panel de clases',
  'Reorganicé los botones “Esta Semana”, “Este Mes” y “Hoy” para que tengan el mismo espacio y no se superpongan.', 22);
addRule(validationSlide, 'validation-divider', 630, 230, 2, 300, '#D9D9D9');
section(validationSlide, 'checks', 690, 230, 520, 'Comprobación realizada',
  'Revisé la sintaxis del controlador y compilé las vistas del sistema. No ejecuté pruebas automatizadas.', 22);
addText(validationSlide, 'current-result',
  'Resultado: una consulta más clara para usuarios y un seguimiento mejor organizado para soporte.',
  { left: 62, top: 545, width: 1140, height: 42 }, { fontSize: 21, bold: true, color: BRAND });
addNotes(validationSlide, 'Fuente: resources/views/partials/dashboard/status_clases_chart.blade.php. Comprobaciones ejecutadas: php -l al controlador y php artisan view:cache; no se corrió una suite de pruebas.');

// Keep the template close and add a simple closing line so the final page is complete.
addText(presentation.slides.getItem(6), 'closing-thanks', 'Gracias', { left: 565, top: 430, width: 400, height: 48 }, { fontSize: 28, bold: true, color: BRAND });
addNotes(presentation.slides.getItem(6), '');

await (await PresentationFile.exportPptx(presentation)).save(candidatePath);
const result = await finalizePresentation({
  explicitTotalSlideCount: 7,
  workspaceDir,
  candidatePath,
  finalPath,
  pythonExecutable: 'C:/Users/carte/.cache/codex-runtimes/codex-primary-runtime/dependencies/python/python.exe',
  integrityValidatorPath: path.join(skillDir, 'container_tools/inspect_presentation_package_integrity.py'),
  layoutValidatorPath: path.join(skillDir, 'container_tools/inspect_presentation_layout_geometry.py'),
  layoutArgs: ['--expected-slide-size-emu', '12192000,6858000', '--validate-bullet-geometry', '--validate-heading-fit'],
  requiredNativeTableOwnerSlides: [],
  requiredNativeChartOwnerSlides: [],
  fontPolicy: {
    basis: 'reference',
    families: ['Montserrat'],
    referencePath: templatePath,
    referenceSha256: '6f03c6fc4aef0e80ce48b129b05d339b6b17f3581d6f4b31df789dd1699debad',
  },
  verifyArtifactToolImport: true,
  receiptPath: path.join(buildDir, 'validation.json'),
});
console.log(JSON.stringify({ finalPath, candidatePath, slideCount: presentation.slides.count, result }, null, 2));









