import { pathToFileURL } from 'node:url'; import path from 'node:path';
const skillDir='C:/Users/carte/.codex/plugins/cache/openai-primary-runtime/presentations/26.930.11008/skills/Presentations';
const {importRuntimeModule}=await import(pathToFileURL(path.join(skillDir,'container_tools/runtime_helpers.mjs')).href);
const {FileBlob,PresentationFile}=await importRuntimeModule('@oai/artifact-tool');
const p=await PresentationFile.importPptx(await FileBlob.load('C:/Users/carte/Downloads/Plantilla_IT_Talcahuano.pptx'));
console.log(JSON.stringify(p.layouts.items.map(l=>({id:l.id,name:l.name,type:l.type})),null,2));
const l=p.layouts.items.find(x=>x.name==='Título y objetos');
const s=p.slides.add({layoutId:l.id});
console.log('Added frame',JSON.stringify(s.frame),'placeholders',JSON.stringify(s.placeholders.summary()));
const out=await s.export({format:'png',scale:1}); const fs=await import('node:fs/promises'); await fs.writeFile('C:/Users/carte/Documents/GitHub/AulaSync/.codex-presentacion-build/probe.png',new Uint8Array(await out.arrayBuffer()));

