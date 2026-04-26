<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Generador de Embeds — Juego de Equipo</title>
<?php require $root . '/views/partials/favicon-links.php'; ?>
<style>
body { font-family: Arial, sans-serif; margin:0; }
header { background:#0e0e0f; color:#fff; padding:12px 16px; }
header h1 { font-size: 1.1rem; margin: 0; }
.container { max-width:1000px; margin:auto; padding:16px; }
label { display:block; margin-top:10px; }
input, select { width:100%; padding:6px; margin-top:4px; box-sizing: border-box; }
.partido { border:1px solid #ccc; padding:12px; margin-top:12px; position:relative; }
.row { display:flex; gap:8px; margin-top:8px; flex-wrap: wrap; }
.row > div { flex:1; min-width: 120px; }
button { margin-top:12px; padding:8px 14px; background:#0e4e8f; color:#fff; border:none; cursor:pointer; }
button:hover { background:#1262b3; }
.delete { position:absolute; top:8px; right:8px; background:#b3261e; }
textarea { width:100%; height:320px; margin-top:16px; box-sizing: border-box; }
.small { font-size:12px; color:#666; }
@media (max-width: 640px) {
  .container { padding: 10px; }
  header h1 { font-size: 0.95rem; }
  textarea { height: 200px; }
}
</style>
</head>

<body>
<header>
<h1>Generador de Embed – Tabla + Resultados</h1>
</header>

<div class="container">

<label>Fecha</label>
<select id="fecha"></select>

<button id="add">Agregar partido</button>
<div id="estado" class="small">Cargando datos…</div>

<div id="partidos"></div>

<button id="generar">Generar código embebido</button>

<textarea id="output" readonly></textarea>
<button id="copiar">Copiar</button>

</div>

<script>
const API = "";
let equipos = [];
let contador = 0;

const $ = id => document.getElementById(id);

function escapeHTML(s){
  return String(s).replace(/[&<>"']/g,m=>({
    "&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"
  }[m]));
}

async function cargarInicial(){
  const [eq, fe] = await Promise.all([
    fetch(API + "/equipos").then(r=>r.json()),
    fetch(API + "/fechas").then(r=>r.json())
  ]);

  equipos = eq;

  fe.forEach(f=>{
    const o = document.createElement("option");
    o.value = f.id;
    o.textContent = "Fecha " + f.numero;
    $("fecha").appendChild(o);
  });

  $("estado").textContent = "Datos cargados";
}

function selectEquipos(){
  const s = document.createElement("select");
  equipos.forEach(e=>{
    const o = document.createElement("option");
    o.value = e.id;
    o.textContent = e.nombre;
    s.appendChild(o);
  });
  return s;
}

$("add").onclick = ()=>{
  contador++;
  const d = document.createElement("div");
  d.className="partido";
  d.innerHTML = `<strong>Partido ${contador}</strong>`;

  const r1=document.createElement("div"); r1.className="row";
  const l=document.createElement("div"); l.append("Local"); l.append(selectEquipos());
  const v=document.createElement("div"); v.append("Visitante"); v.append(selectEquipos());
  r1.append(l,v);

  const r2=document.createElement("div"); r2.className="row";
  r2.innerHTML=`<div>GL<input type="number" value="0"></div><div>GV<input type="number" value="0"></div>`;

  const del=document.createElement("button");
  del.textContent="Eliminar"; del.className="delete";
  del.onclick=()=>d.remove();

  d.append(r1,r2,del);
  $("partidos").appendChild(d);
};

$("generar").onclick = async ()=>{
  const fechaId = $("fecha").value;
  const cards = [...document.querySelectorAll(".partido")];

  const partidos = cards.map(c=>{
    const s=c.querySelectorAll("select");
    const i=c.querySelectorAll("input");
    return {
      local_id:+s[0].value,
      visitante_id:+s[1].value,
      goles_local:+i[0].value,
      goles_visitante:+i[1].value
    };
  });

  await fetch(API + "/partidos",{
    method:"POST",
    headers:{ "Content-Type":"application/json" },
    body:JSON.stringify({ fecha_id:fechaId, partidos })
  });

  const data = await fetch(API + "/tabla/" + fechaId).then(r=>r.json());

  $("output").value = `
<main>
<h1>ESTADÍSTICAS GENERALES</h1>
<h2>Fecha ${data.fecha}</h2>

<table border="1" cellpadding="6">
<tr><th>Pos</th><th>Equipo</th><th>PJ</th><th>Pts</th></tr>
${data.tabla.map((e,i)=>`
<tr>
<td>${i+1}</td>
<td>${escapeHTML(e.equipo)}</td>
<td>${e.pj}</td>
<td>${e.puntos}</td>
</tr>`).join("")}
</table>

<h2>Resultados</h2>
${data.resultados.map(r=>`
<div>${r.local} ${r.gl}-${r.gv} ${r.visitante}</div>`).join("")}
</main>
`.trim();
};

$("copiar").onclick=()=>{
  $("output").select();
  document.execCommand("copy");
};

cargarInicial();
</script>
</body>
</html>
