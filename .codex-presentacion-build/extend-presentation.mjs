import fs from 'node:fs/promises';
import path from 'node:path';
import { pathToFileURL } from 'node:url';

const skillDir = 'C:/Users/carte/.codex/plugins/cache/openai-primary-runtime/presentations/26.930.11008/skills/Presentations';
const workspaceDir = 'C:/Users/carte/Documents/GitHub/AulaSync';
const buildDir = path.join(workspaceDir, '.codex-presentacion-build');
const sourcePath = path.join(workspaceDir, 'docs', 'Informe_AulaSync_Presentacion.pptx');
const candidatePath = path.join(buildDir, 'candidate-complete.pptx');
const finalPath = path.join(workspaceDir, 'docs', 'Informe_AulaSync_Presentacion_Completa.pptx');
const { importRuntimeModule } = await import(pathToFileURL(path.join(skillDir, 'container_tools/runtime_helpers.mjs')).href);
const { FileBlob, PresentationFile } = await importRuntimeModule('@oai/artifact-tool');
const { finalizePresentation } = await import(pathToFileURL(path.join(skillDir, 'container_tools/artifact_tool_utils.mjs')).href);
const presentation = await PresentationFile.importPptx(await FileBlob.load(sourcePath));
if (await fs.stat(finalPath).then(() => true).catch(() => false)) throw new Error(`Output already exists: ${finalPath}`);

const snapshot = await presentation.inspect({ kind: 'slide,textbox,shape', maxChars: 30000 });
const records = snapshot.ndjson.split('\n').filter(Boolean).map(line => JSON.parse(line));
function findText(slideIndex, name) {
  const record = records.find(item => item.slideIndex === slideIndex && item.name === name);
  if (!record) throw new Error(`Could not locate ${name} on slide ${slideIndex + 1}`);
  return presentation.resolve(record.id);
}

// Make the user's role, per-site limits and ticket ownership explicit.
findText(3, 'permissions-body').text = 'Agregué el rol Soporte y el permiso para atender solicitudes. Ajusté el acceso para que el equipo gestione solo tickets de su sede. Cada usuario puede seguir revisando sus propios tickets.';

const BRAND = '#D00120';
const BODY = '#646363';
const FONT = 'Montserrat';
function addText(slide, name, text, position, style = {}) {
  const shape = slide.shapes.add({ geometry: 'textbox', name, position, fill: 'none', line: { style: 'solid', fill: 'none', width: 0 } });
  shape.text = text;
  shape.text.style = {
    typeface: FONT, fontSize: style.fontSize ?? 22, bold: style.bold ?? false,
    color: style.color ?? BODY, alignment: 'left', verticalAlignment: 'top',
    autoFit: 'shrinkText', wrap: 'square',
    insets: { top: 0, right: 0, bottom: 0, left: 0 },
  };
  return shape;
}
function addRule(slide, name, left, top, width, height, color = BRAND) {
  return slide.shapes.add({ geometry: 'rect', name, position: { left, top, width, height }, fill: color, line: { style: 'solid', fill: color, width: 0 } });
}
function section(slide, name, left, top, width, heading, body) {
  addText(slide, `${name}-heading`, heading, { left, top, width, height: 38 }, { fontSize: 24, bold: true, color: BRAND });
  addRule(slide, `${name}-accent`, left, top + 44, 42, 4);
  addText(slide, `${name}-body`, body, { left, top: top + 62, width, height: 280 }, { fontSize: 21 });
}

// Insert an additional slide for the earlier support dashboard and access work.
const inserted = presentation.slides.insert({ after: presentation.slides.getItem(4), layoutId: '/ppt/slideLayouts/slideLayout2.xml' });
const panelSlide = inserted.slide;
addText(panelSlide, 'panel-title', 'Panel de soporte', { left: 43.13, top: 38.33, width: 1196.52, height: 100 }, { fontSize: 58.67, color: BRAND });
section(panelSlide, 'support-team', 62, 230, 510, 'Vista para el equipo',
  'Agregué una página de soporte y un acceso desde el menú. Permite revisar las solicitudes abiertas y en proceso, ver un resumen y filtrar por sede.');
addRule(panelSlide, 'support-panel-divider', 630, 230, 2, 300, '#D9D9D9');
section(panelSlide, 'support-preview', 690, 230, 520, 'Acceso de prueba',
  'También dejé disponible una vista previa para revisar el panel. Si la persona todavía no tiene el rol Soporte, solo puede ver sus propias solicitudes. Corregí además el acceso directo para respetar los permisos y la sede.');
panelSlide.speakerNotes.textFrame.setText('Fuentes: routes/web.php; resources/views/components/sidebar/content.blade.php; resources/views/soporte/dashboard.blade.php; app/Http/Controllers/SupportTicketController.php; database/seeders/RoleSeeder.php.');

await (await PresentationFile.exportPptx(presentation)).save(candidatePath);
const result = await finalizePresentation({
  explicitTotalSlideCount: 8,
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
    basis: 'reference', families: ['Montserrat'],
    referencePath: 'C:/Users/carte/Downloads/Plantilla_IT_Talcahuano.pptx',
    referenceSha256: '6f03c6fc4aef0e80ce48b129b05d339b6b17f3581d6f4b31df789dd1699debad',
  },
  verifyArtifactToolImport: true,
  receiptPath: path.join(buildDir, 'validation-complete.json'),
});
console.log(JSON.stringify({ finalPath, candidatePath, slideCount: presentation.slides.count, result }, null, 2));
