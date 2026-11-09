import { pathToFileURL } from 'node:url';
import path from 'node:path';
const skillDir = 'C:/Users/carte/.codex/plugins/cache/openai-primary-runtime/presentations/26.930.11008/skills/Presentations';
const { importRuntimeModule } = await import(pathToFileURL(path.join(skillDir, 'container_tools/runtime_helpers.mjs')).href);
const { FileBlob, PresentationFile } = await importRuntimeModule('@oai/artifact-tool');
const p = await PresentationFile.importPptx(await FileBlob.load('C:/Users/carte/Downloads/Plantilla_IT_Talcahuano.pptx'));
console.log((await p.inspect({kind:'deck,slide,textbox,shape,image,layout,notes', maxChars:30000})).ndjson);
console.log('COUNTS', JSON.stringify({slides:p.slides.count, layouts:p.layouts.items.length, masters:p.masters.items.length}));
for (let i=0;i<p.slides.count;i++) { const s=p.slides.getItem(i); const x=await s.export({format:'layout'}); console.log('LAYOUT', i+1, await x.text()); }
